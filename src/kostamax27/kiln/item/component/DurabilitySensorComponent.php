<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\item\component\value\DurabilityThreshold;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;

final class DurabilitySensorComponent implements ItemComponent{

	public const NAME = "minecraft:durability_sensor";

	/**
	 * @param non-empty-list<DurabilityThreshold> $durability_thresholds
	 */
	public function __construct(
		readonly public array $durability_thresholds
	){
		TypeValidator::validateNonEmptyList("Durability thresholds", $durability_thresholds);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_20){
			$components->setTag(self::NAME, CompoundTag::create()->setTag("durability_thresholds", new ListTag(array_map(static fn(DurabilityThreshold $threshold) : CompoundTag => $threshold->toNbt(), $this->durability_thresholds), NBT::TAG_Compound)));
		}
	}
}