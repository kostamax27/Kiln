<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\item\component\value\ItemDescriptor;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;

final class StorageItemComponent implements ItemComponent{

	public const NAME = "minecraft:storage_item";

	/**
	 * @param int $max_slots maximum number of stacks stored, [1, 64].
	 * @param bool $allow_nested_storage_items whether other storage items may be stored inside.
	 * @param list<ItemDescriptor> $allowed_items items exclusively allowed inside, empty for any item.
	 * @param list<ItemDescriptor> $banned_items items never allowed inside.
	 */
	public function __construct(
		readonly public int $max_slots = 64,
		readonly public bool $allow_nested_storage_items = true,
		readonly public array $allowed_items = [],
		readonly public array $banned_items = []
	){
		TypeValidator::validateInt("Storage max slots", $max_slots, 1, 64);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_110){
			$components->setTag(self::NAME, CompoundTag::create()
				->setByte("allow_nested_storage_items", $this->allow_nested_storage_items ? 1 : 0)
				->setTag("allowed_items", new ListTag(array_map(static fn(ItemDescriptor $item) : CompoundTag => $item->toNbt(), $this->allowed_items), NBT::TAG_Compound))
				->setTag("banned_items", new ListTag(array_map(static fn(ItemDescriptor $item) : CompoundTag => $item->toNbt(), $this->banned_items), NBT::TAG_Compound))
				->setInt("max_slots", $this->max_slots));
		}
	}
}