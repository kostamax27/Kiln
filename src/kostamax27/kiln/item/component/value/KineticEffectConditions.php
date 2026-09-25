<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component\value;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class KineticEffectConditions{

	/**
	 * @param int $max_duration ticks after the delay during which the effect applies, -1 for indefinitely.
	 * @param float $min_speed minimum speed of the user along the view vector.
	 * @param float $min_relative_speed minimum speed of the user relative to the target.
	 */
	public function __construct(
		readonly public int $max_duration = -1,
		readonly public float $min_speed = 0.0,
		readonly public float $min_relative_speed = 0.0
	){
		TypeValidator::validateInt("Kinetic effect max duration", $max_duration, -1, 32767);
		TypeValidator::validateFloat("Kinetic effect min speed", $min_speed, 0.0);
		TypeValidator::validateFloat("Kinetic effect min relative speed", $min_relative_speed);
	}

	public function toNbt() : CompoundTag{
		return CompoundTag::create()
			->setShort("max_duration", $this->max_duration)
			->setFloat("min_relative_speed", $this->min_relative_speed)
			->setFloat("min_speed", $this->min_speed);
	}
}
