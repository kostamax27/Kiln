<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class DurabilityComponent implements ItemComponent{

	public const NAME = "minecraft:durability";

	/**
	 * @param int $max_durability damage the item takes before breaking.
	 * @param int $min_damage_chance minimum chance in percent that a use costs durability.
	 * @param int $max_damage_chance maximum chance in percent that a use costs durability.
	 */
	public function __construct(
		readonly public int $max_durability,
		readonly public int $min_damage_chance = 100,
		readonly public int $max_damage_chance = 100
	){
		TypeValidator::validateInt("Max durability", $max_durability, 0);
		TypeValidator::validateInt("Min damage chance", $min_damage_chance, 0, 100);
		TypeValidator::validateInt("Max damage chance", $max_damage_chance, $min_damage_chance, 100);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()
			->setTag("damage_chance", CompoundTag::create()
				->setInt("max", $this->max_damage_chance)
				->setInt("min", $this->min_damage_chance))
			->setInt("max_durability", $this->max_durability));
	}
}