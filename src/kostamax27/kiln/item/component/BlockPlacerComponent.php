<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\item\component\value\BlockDescriptor;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;
use function count;

final class BlockPlacerComponent implements ItemComponent{

	public const NAME = "minecraft:block_placer";

	/**
	 * @param string $block identifier of the block the client predicts being placed.
	 * @param list<BlockDescriptor> $use_on blocks the item may be used on, empty for any block.
	 * @param bool $use_block_as_icon whether the placed block is rendered as the item icon.
	 * @param bool $replace_block_item whether this item replaces the block's own item.
	 * @param bool $aligned_placement whether placement is aligned while the use button is held.
	 */
	public function __construct(
		readonly public string $block,
		readonly public array $use_on = [],
		readonly public bool $use_block_as_icon = false,
		readonly public bool $replace_block_item = false,
		readonly public bool $aligned_placement = false
	){
		TypeValidator::validateIdentifier("Block placer block", $block);
		TypeValidator::validateInt("Block placer use_on entry count", count($use_on), 0, 256);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id < ProtocolVersions::V1_21_50){
			return;
		}
		$components->setTag(self::NAME, CompoundTag::create()
			->setByte("alignedPlacement", $this->aligned_placement ? 1 : 0)
			->setString("block", $this->block)
			->setByte("canUseBlockAsIcon", $this->use_block_as_icon ? 1 : 0)
			->setByte("replaceBlockItem", $this->replace_block_item ? 1 : 0)
			->setTag("use_on", new ListTag(array_map(static fn(BlockDescriptor $block) : CompoundTag => $block->toNbt(), $this->use_on), NBT::TAG_Compound)));
	}
}