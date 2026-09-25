<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component\value;

use InvalidArgumentException;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class FloatRange{

	/**
	 * @param float $min inclusive lower bound, >= 0.
	 * @param float $max inclusive upper bound, >= $min.
	 */
	public function __construct(
		readonly public float $min,
		readonly public float $max
	){
		TypeValidator::validateFloat("Range minimum", $min, 0.0);
		TypeValidator::validateFloat("Range maximum", $max, 0.0);
		$min <= $max || throw new InvalidArgumentException("Range minimum must be <= maximum, got [{$min}, {$max}]");
	}

	public function toNbt() : CompoundTag{
		return CompoundTag::create()
			->setFloat("max", $this->max)
			->setFloat("min", $this->min);
	}
}