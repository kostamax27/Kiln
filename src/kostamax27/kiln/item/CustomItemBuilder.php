<?php

declare(strict_types=1);

namespace kostamax27\kiln\item;

use Closure;
use InvalidArgumentException;
use kostamax27\kiln\creative\CreativeInfo;
use kostamax27\kiln\item\component\DisplayNameComponent;
use kostamax27\kiln\item\component\IconComponent;
use kostamax27\kiln\item\component\ItemComponent;
use kostamax27\kiln\item\property\ItemProperty;
use kostamax27\kiln\item\resolver\ItemComponentResolver;
use kostamax27\kiln\item\resolver\VanillaItemComponentResolver;
use kostamax27\kiln\item\state\ItemStates;
use kostamax27\kiln\item\state\VariantNameDescriber;
use pocketmine\data\bedrock\item\ItemTypeSerializeException;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemTypeIds;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionMethod;
use function explode;
use function preg_match;

final class CustomItemBuilder{

	public const IDENTIFIER_PATTERN = "/^[a-z0-9_.\-]+:[a-z0-9_.\/\-]+$/";
	public const VARIANT_NAME_PATTERN = "/^[a-z0-9_]+$/";

	/**
	 * Starts building a custom item definition. Items that describe state in describeState() are
	 * shown to clients as one item per state, named "{$identifier}_{$variant}".
	 *
	 * @param string $identifier namespaced identifier, e.g. "myplugin:ruby". The "minecraft" namespace is reserved.
	 * @param Item $item server-side prototype of the item. Its type ID must come from {@see ItemTypeIds::newId()}.
	 * @param string $icon texture key in the resource pack's item_texture.json; variants use "{$icon}_{$variant}".
	 */
	public static function create(string $identifier, Item $item, string $icon) : self{
		preg_match(self::IDENTIFIER_PATTERN, $identifier) === 1 || throw new InvalidArgumentException("Identifier \"{$identifier}\" must match " . self::IDENTIFIER_PATTERN);
		[$namespace, ] = explode(":", $identifier, 2);
		$namespace !== "minecraft" || throw new InvalidArgumentException("Identifier \"{$identifier}\" cannot use the reserved \"minecraft\" namespace");
		return new self($identifier, clone $item, $icon, false, [], [], CreativeInfo::items(), VanillaItemComponentResolver::instance());
	}

	/**
	 * Starts building an override of an item that already exists, such as a vanilla item.
	 *
	 * @param Item $item the item to override, e.g. VanillaItems::IRON_SWORD().
	 * @param string $icon texture key in item_texture.json. Many vanilla items have no key of their own (iron_sword is
	 * an entry of the "sword" array), so point a key of your resource pack at their texture file.
	 */
	public static function override(Item $item, string $icon) : self{
		!($item instanceof ItemBlock) || throw new InvalidArgumentException("Block items cannot be overridden, got " . $item->getName());
		try{
			$identifier = GlobalItemDataHandlers::getSerializer()->serializeType($item)->getName();
		}catch(ItemTypeSerializeException $e){
			throw new InvalidArgumentException("Item {$item->getName()} cannot be serialized: {$e->getMessage()}", 0, $e);
		}
		[, $path] = explode(":", $identifier, 2);
		return new self($identifier, clone $item, $icon, true, [new DisplayNameComponent("item.{$path}.name")], [], CreativeInfo::fromInventory(CreativeInventory::getInstance(), $item), VanillaItemComponentResolver::instance());
	}

	/** @var (Closure(Item) : string)|null */
	private ?Closure $variant_names = null;

	/** @var list<Closure(Item) : list<ItemComponent>> */
	private array $variant_components = [];

	/**
	 * @param list<ItemComponent> $components
	 * @param list<ItemProperty> $properties
	 */
	private function __construct(
		public string $identifier,
		public Item $item,
		public string $icon,
		readonly private bool $override,
		public array $components,
		public array $properties,
		public CreativeInfo $creative_info,
		public ItemComponentResolver $component_resolver
	){}

	public function setCreativeInfo(CreativeInfo $creative_info) : self{
		$this->creative_info = $creative_info;
		return $this;
	}

	/**
	 * Replaces the strategy that derives components from the server-side item.
	 */
	public function setComponentResolver(ItemComponentResolver $component_resolver) : self{
		$this->component_resolver = $component_resolver;
		return $this;
	}

	/**
	 * Adds components to the item, or to every variant of it.
	 */
	public function addComponent(ItemComponent ...$components) : self{
		foreach($components as $component){
			$this->components[] = $component;
		}
		return $this;
	}

	/**
	 * Adds legacy item properties (the "item_properties" compound) to the item.
	 */
	public function addProperty(ItemProperty ...$properties) : self{
		foreach($properties as $property){
			$this->properties[] = $property;
		}
		return $this;
	}

	/**
	 * Names variants instead of joining their described values (enum cases become their
	 * lowercase names, e.g. "ruby").
	 *
	 * @param Closure(Item) : string $names returns the name of the variant the given item is in.
	 */
	public function setVariantNames(Closure $names) : self{
		$this->variant_names = $names;
		return $this;
	}

	/**
	 * Adds components that depend on the variant, e.g. a different rarity per gem type.
	 *
	 * @param Closure(Item) : list<ItemComponent> $components returns the components of the variant the given item is in.
	 */
	public function addVariantComponents(Closure $components) : self{
		$this->variant_components[] = $components;
		return $this;
	}

	/**
	 * Builds the item definition, with one variant per state of the item.
	 */
	public function build() : CustomItem{
		$states = $this->override ? [$this->item->getStateId() => clone $this->item] : ItemStates::enumerate($this->item);
		$single = count($states) === 1;
		!$single || count($this->variant_components) === 0 || $this->override || throw new InvalidArgumentException("Item \"{$this->identifier}\" has no state, add its components with addComponent()");

		$variants = [];
		$identifiers = [];
		foreach($states as $state_id => $state){
			if($single){
				[$identifier, $icon, $network_id] = [$this->identifier, $this->icon, $state->getTypeId()];
			}else{
				$name = $this->nameVariant($state);
				[$identifier, $icon, $network_id] = ["{$this->identifier}_{$name}", "{$this->icon}_{$name}", ItemTypeIds::newId()];
			}
			!isset($identifiers[$identifier]) || throw new InvalidArgumentException("Item \"{$this->identifier}\" has more than one variant named \"{$identifier}\"");
			$identifiers[$identifier] = true;

			$components = [];
			foreach([...$this->component_resolver->resolveComponents($identifier, $state), new IconComponent($icon), ...$this->components, ...$this->resolveVariantComponents($state)] as $component){
				$components[$component->getName()] = $component;
			}
			$properties = [];
			foreach([...$this->component_resolver->resolveProperties($identifier, $state), ...$this->properties] as $property){
				$properties[$property->getName()] = $property;
			}
			$variants[$state_id] = new CustomItemVariant($identifier, clone $state, $network_id, $components, $properties, $this->creative_info);
		}
		return new CustomItem($this->identifier, clone $this->item, $variants);
	}

	private function nameVariant(Item $item) : string{
		if($this->variant_names !== null){
			$name = ($this->variant_names)(clone $item);
		}else{
			$describer = new VariantNameDescriber();
			(new ReflectionMethod($item, "describeState"))->invoke(clone $item, $describer);
			$name = $describer->getName();
		}
		preg_match(self::VARIANT_NAME_PATTERN, $name) === 1 || throw new InvalidArgumentException("Variant name of item \"{$this->identifier}\" must match " . self::VARIANT_NAME_PATTERN . ", got \"{$name}\"");
		return $name;
	}

	/**
	 * @return list<ItemComponent>
	 */
	private function resolveVariantComponents(Item $item) : array{
		$components = [];
		foreach($this->variant_components as $factory){
			foreach($factory(clone $item) as $component){
				$components[] = $component;
			}
		}
		return $components;
	}
}
