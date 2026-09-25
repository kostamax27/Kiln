<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\item\component\value\DiggerSpeed;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;

final class DiggerComponent implements ItemComponent{

	public const NAME = "minecraft:digger";

	/**
	 * @param non-empty-list<DiggerSpeed> $destroy_speeds
	 * @param bool $use_efficiency whether the efficiency enchantment speeds digging up.
	 */
	public function __construct(
		readonly public array $destroy_speeds,
		readonly public bool $use_efficiency = true
	){
		TypeValidator::validateNonEmptyList("Digger destroy speeds", $destroy_speeds);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_20_30){
			$components->setTag(self::NAME, CompoundTag::create()
				->setTag("destroy_speeds", new ListTag(array_map(static fn(DiggerSpeed $speed) : CompoundTag => $speed->toNbt(), $this->destroy_speeds), NBT::TAG_Compound))
				->setByte("use_efficiency", $this->use_efficiency ? 1 : 0));
		}
	}
}
