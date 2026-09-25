<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component\value;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class RandomOffsetAxis{

	/**
	 * @param float $min lowest offset in pixels, [-16, 16].
	 * @param float $max highest offset in pixels, [$min, 16].
	 * @param int $steps number of evenly spaced offsets between min and max, 0 for continuous.
	 */
	public function __construct(
		readonly public float $min = 0.0,
		readonly public float $max = 0.0,
		readonly public int $steps = 0
	){
		TypeValidator::validateFloat("Random offset minimum", $min, -16.0, 16.0);
		TypeValidator::validateFloat("Random offset maximum", $max, $min, 16.0);
		TypeValidator::validateInt("Random offset steps", $steps, 0);
	}

	public function toNbt() : CompoundTag{
		return CompoundTag::create()
			->setTag("range", CompoundTag::create()
				->setFloat("max", $this->max)
				->setFloat("min", $this->min))
			->setInt("steps", $this->steps);
	}
}
