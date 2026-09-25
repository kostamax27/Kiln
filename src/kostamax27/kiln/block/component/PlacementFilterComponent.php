<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\block\component\value\PlacementCondition;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;
use function count;

final class PlacementFilterComponent implements BlockComponent{

	public const NAME = "minecraft:placement_filter";

	/**
	 * @param non-empty-list<PlacementCondition> $conditions the block can be placed if any condition matches.
	 */
	public function __construct(
		readonly public array $conditions
	){
		TypeValidator::validateInt("Placement condition count", count(TypeValidator::validateNonEmptyList("Placement conditions", $conditions)), 1, 64);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()->setTag("conditions", new ListTag(array_map(static fn(PlacementCondition $condition) : CompoundTag => $condition->toNbt(), $this->conditions), NBT::TAG_Compound)));
	}
}