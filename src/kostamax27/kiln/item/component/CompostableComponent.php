<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class CompostableComponent implements ItemComponent{

	public const NAME = "minecraft:compostable";

	/**
	 * @param int $composting_chance chance in percent to add a layer to a composter, [1, 100].
	 */
	public function __construct(
		readonly public int $composting_chance
	){
		TypeValidator::validateInt("Composting chance", $composting_chance, 1, 100);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_60){
			$components->setTag(self::NAME, CompoundTag::create()->setByte("composting_chance", $this->composting_chance));
		}
	}
}