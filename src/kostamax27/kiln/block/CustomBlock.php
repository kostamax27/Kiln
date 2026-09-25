<?php

declare(strict_types=1);

namespace kostamax27\kiln\block;

use Closure;
use kostamax27\kiln\block\component\BlockComponent;
use kostamax27\kiln\block\permutation\Permutation;
use kostamax27\kiln\block\state\BlockProperty;
use kostamax27\kiln\creative\CreativeInfo;
use pocketmine\block\Block;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use function array_map;
use function count;

final class CustomBlock{

	public const MOLANG_VERSION = 1;

	/**
	 * Use {@see CustomBlockBuilder} rather than constructing this directly.
	 *
	 * @param string $identifier namespaced block identifier.
	 * @param Closure() : Block $factory creates the block; called again on every async worker.
	 * @param Block $block server-side prototype of the block.
	 * @param list<string> $state_names names of the described states, one per describing call.
	 * @param list<BlockProperty> $properties Bedrock properties derived from the described states.
	 * @param array<string, BlockComponent> $components keyed by {@see BlockComponent::getName()}.
	 * @param list<Permutation> $permutations
	 * @param list<string> $tags block tags.
	 */
	public function __construct(
		readonly public string $identifier,
		readonly public Closure $factory,
		readonly public Block $block,
		readonly public array $state_names,
		readonly public array $properties,
		readonly public array $components,
		readonly public array $permutations,
		readonly public array $tags,
		readonly public CreativeInfo $creative_info
	){}

	/**
	 * Encodes the block definition sent to clients of the given protocol in the StartGame packet.
	 *
	 * @param int $block_id numeric block ID assigned by {@see CustomBlockRegistry}.
	 */
	public function encode(int $protocol_id, int $block_id) : CompoundTag{
		$components = CompoundTag::create();
		foreach($this->components as $component){
			$component->write($components, $protocol_id);
		}
		foreach($this->tags as $tag){
			$components->setTag("tag:{$tag}", CompoundTag::create());
		}
		$nbt = CompoundTag::create()
			->setTag("components", $components)
			->setTag("menu_category", CompoundTag::create()
				->setString("category", $this->creative_info->toMenuCategory())
				->setString("group", $this->creative_info->toMenuGroup())
				->setByte("is_hidden_in_commands", 0))
			->setInt("molangVersion", self::MOLANG_VERSION)
			->setTag("permutations", new ListTag(array_map(static fn(Permutation $permutation) : CompoundTag => $permutation->toNbt($protocol_id), $this->permutations), NBT::TAG_Compound))
			->setTag("properties", new ListTag(array_map(static fn(BlockProperty $property) : CompoundTag => $property->toNbt(), $this->properties), NBT::TAG_Compound))
			->setTag("vanilla_block_data", CompoundTag::create()->setInt("block_id", $block_id));
		if(count($this->tags) > 0){
			$nbt->setTag("blockTags", new ListTag(array_map(static fn(string $tag) : StringTag => new StringTag($tag), $this->tags), NBT::TAG_String));
		}
		return $nbt;
	}
}