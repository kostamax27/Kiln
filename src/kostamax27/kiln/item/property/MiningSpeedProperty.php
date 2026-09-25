<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\property;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class MiningSpeedProperty implements ItemProperty{

	public const NAME = "mining_speed";

	/**
	 * @param float $value base mining speed multiplier used when no digger speed applies.
	 */
	public function __construct(
		readonly public float $value
	){
		TypeValidator::validateFloat("Mining speed", $value, 0.0);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $properties, int $protocol_id) : void{
		$properties->setFloat(self::NAME, $this->value);
	}
}