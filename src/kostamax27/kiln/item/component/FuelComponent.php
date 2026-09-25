<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class FuelComponent implements ItemComponent{

	public const NAME = "minecraft:fuel";

	/**
	 * @param float $duration seconds this item burns for in a furnace, >= 0.05.
	 */
	public function __construct(
		readonly public float $duration
	){
		TypeValidator::validateFloat("Fuel duration", $duration, 0.05);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()->setFloat("duration", $this->duration));
	}
}