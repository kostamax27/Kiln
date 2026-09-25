<?php

declare(strict_types=1);

namespace kostamax27\kiln\block;

use Closure;
use InvalidArgumentException;
use kostamax27\kiln\block\component\BlockComponent;
use kostamax27\kiln\block\component\GeometryComponent;
use kostamax27\kiln\block\component\MaterialInstancesComponent;
use kostamax27\kiln\block\permutation\Permutation;
use kostamax27\kiln\block\resolver\BlockComponentResolver;
use kostamax27\kiln\block\resolver\VanillaBlockComponentResolver;
use kostamax27\kiln\creative\CreativeInfo;
use kostamax27\kiln\item\CustomItemBuilder;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\block\Block;
use ReflectionFunction;
use function array_unique;
use function array_values;
use function count;
use function explode;
use function get_debug_type;
use function is_array;
use function is_scalar;
use function preg_match;

final class CustomBlockBuilder{

	public const MAX_STATES = 65536;

	/**
	 * Starts building a custom block definition.
	 *
	 * @param string $identifier namespaced identifier, e.g. "myplugin:ruby_ore". The "minecraft" namespace is reserved.
	 * @param Closure() : Block $factory
	 */
	public static function create(string $identifier, Closure $factory) : self{
		preg_match(CustomItemBuilder::IDENTIFIER_PATTERN, $identifier) === 1 || throw new InvalidArgumentException("Identifier \"{$identifier}\" must match " . CustomItemBuilder::IDENTIFIER_PATTERN);
		[$namespace, ] = explode(":", $identifier, 2);
		$namespace !== "minecraft" || throw new InvalidArgumentException("Identifier \"{$identifier}\" cannot use the reserved \"minecraft\" namespace");

		$reflection = new ReflectionFunction($factory);
		$reflection->getClosureThis() === null || throw new InvalidArgumentException("Factory of block \"{$identifier}\" must be a static closure, since it is copied to async workers");
		foreach($reflection->getClosureUsedVariables() as $name => $value){
			self::isThreadSafeValue($value) || throw new InvalidArgumentException("Factory of block \"{$identifier}\" may only capture scalars and arrays of scalars, \${$name} is " . get_debug_type($value));
		}
		$block = self::callFactory($identifier, $factory);
		$other = self::callFactory($identifier, $factory);
		$other->getTypeId() === $block->getTypeId() && $other::class === $block::class || throw new InvalidArgumentException("Factory of block \"{$identifier}\" must return the same block type on every call, allocate the type ID outside of the factory");

		return new self($identifier, $factory, $block, [], [], [], [], CreativeInfo::construction(), VanillaBlockComponentResolver::instance());
	}

	private static function callFactory(string $identifier, Closure $factory) : Block{
		$block = $factory();
		$block instanceof Block || throw new InvalidArgumentException("Factory of block \"{$identifier}\" must return a " . Block::class . ", got " . get_debug_type($block));
		return $block;
	}

	private static function isThreadSafeValue(mixed $value) : bool{
		if(is_array($value)){
			foreach($value as $entry){
				if(!self::isThreadSafeValue($entry)){
					return false;
				}
			}
			return true;
		}
		return $value === null || is_scalar($value);
	}

	/**
	 * @param Closure() : Block $factory
	 * @param list<string> $state_names
	 * @param list<BlockComponent> $components
	 * @param list<Permutation> $permutations
	 * @param list<string> $tags
	 */
	private function __construct(
		public string $identifier,
		public Closure $factory,
		public Block $block,
		public array $state_names,
		public array $components,
		public array $permutations,
		public array $tags,
		public CreativeInfo $creative_info,
		public BlockComponentResolver $component_resolver
	){}

	public function setCreativeInfo(CreativeInfo $creative_info) : self{
		$this->creative_info = $creative_info;
		return $this;
	}

	/**
	 * Replaces the strategy that derives components from the server-side block.
	 */
	public function setComponentResolver(BlockComponentResolver $component_resolver) : self{
		$this->component_resolver = $component_resolver;
		return $this;
	}

	/**
	 * Names the states the block describes in describeBlockItemState() and describeBlockOnlyState(), one
	 * name per describing call and in the same order.
	 *
	 * @param string ...$names namespaced names, e.g. "myplugin:stage".
	 */
	public function setStateNames(string ...$names) : self{
		$this->state_names = array_values($names);
		return $this;
	}

	/**
	 * Adds components to the block.
	 */
	public function addComponent(BlockComponent ...$components) : self{
		foreach($components as $component){
			$this->components[] = $component;
		}
		return $this;
	}

	/**
	 * Adds components that apply only while the block is in certain states.
	 */
	public function addPermutation(Permutation ...$permutations) : self{
		foreach($permutations as $permutation){
			$this->permutations[] = $permutation;
		}
		return $this;
	}

	/**
	 * Adds block tags, e.g. "stone" or "minecraft:is_pickaxe_item_destructible", which item digger speeds
	 * and Molang queries match against.
	 */
	public function addTag(string ...$tags) : self{
		foreach($tags as $tag){
			$this->tags[] = TypeValidator::validateTag("Block tag", $tag);
		}
		return $this;
	}

	/**
	 * Builds the block definition, deriving its properties from the described states and validating
	 * permutations against them.
	 */
	public function build() : CustomBlock{
		$properties = [];
		$state_count = 1;
		foreach(BlockRuntimeRegistrar::describeProperties($this->block, $this->state_names) as $property){
			!isset($properties[$property->name]) || throw new InvalidArgumentException("Block \"{$this->identifier}\" has more than one state named \"{$property->name}\"");
			$properties[$property->name] = $property;
			$state_count *= count($property->values);
		}
		TypeValidator::validateInt("State count of block \"{$this->identifier}\"", $state_count, 1, self::MAX_STATES);

		foreach($this->permutations as $permutation){
			foreach($permutation->conditions as $condition){
				isset($properties[$condition->name]) || throw new InvalidArgumentException("Permutation \"{$permutation->condition}\" of block \"{$this->identifier}\" refers to unknown state \"{$condition->name}\"");
				$properties[$condition->name]->accepts($condition->value) || throw new InvalidArgumentException("Permutation \"{$permutation->condition}\" of block \"{$this->identifier}\" compares \"{$condition->name}\" with a value it cannot hold");
			}
		}

		$components = [];
		foreach([...$this->component_resolver->resolve($this->identifier, $this->block), ...$this->components] as $component){
			$components[$component->getName()] = $component;
		}
		isset($components[GeometryComponent::NAME]) === isset($components[MaterialInstancesComponent::NAME]) || throw new InvalidArgumentException("Block \"{$this->identifier}\" must define both geometry and material instances, or neither");

		return new CustomBlock($this->identifier, $this->factory, clone $this->block, $this->state_names, array_values($properties), $components, $this->permutations, array_values(array_unique($this->tags)), $this->creative_info);
	}
}
