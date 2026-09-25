<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\item\component\value\RepairItem;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;

final class RepairableComponent implements ItemComponent{

	public const NAME = "minecraft:repairable";

	/**
	 * @param non-empty-list<RepairItem> $repair_items
	 */
	public function __construct(
		readonly public array $repair_items
	){
		TypeValidator::validateNonEmptyList("Repair items", $repair_items);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_20_10){
			$components->setTag(self::NAME, CompoundTag::create()->setTag("repair_items", new ListTag(array_map(static fn(RepairItem $item) : CompoundTag => $item->toNbt(), $this->repair_items), NBT::TAG_Compound)));
		}
	}
}