<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component\value;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use function max;
use function min;

final class Box{

	/**
	 * Creates a box from block pixel coordinates, where a full block spans from 0 to 16 on every axis.
	 */
	public static function pixels(float $min_x, float $min_y, float $min_z, float $max_x, float $max_y, float $max_z) : self{
		return new self($min_x, $min_y, $min_z, $max_x, $max_y, $max_z);
	}

	/**
	 * Creates a box spanning the whole block.
	 */
	public static function full() : self{
		return new self(0.0, 0.0, 0.0, 16.0, 16.0, 16.0);
	}

	private function __construct(
		readonly public float $min_x,
		readonly public float $min_y,
		readonly public float $min_z,
		readonly public float $max_x,
		readonly public float $max_y,
		readonly public float $max_z
	){
		TypeValidator::validateFloat("Box min x", $min_x, 0.0, 16.0);
		TypeValidator::validateFloat("Box min y", $min_y, 0.0, 24.0);
		TypeValidator::validateFloat("Box min z", $min_z, 0.0, 16.0);
		TypeValidator::validateFloat("Box max x", $max_x, $min_x, 16.0);
		TypeValidator::validateFloat("Box max y", $max_y, $min_y, 24.0);
		TypeValidator::validateFloat("Box max z", $max_z, $min_z, 16.0);
	}

	/**
	 * Returns the smallest box containing every given box.
	 *
	 * @param non-empty-list<self> $boxes
	 */
	public static function union(array $boxes) : self{
		$union = $boxes[0];
		foreach($boxes as $box){
			$union = new self(
				min($union->min_x, $box->min_x), min($union->min_y, $box->min_y), min($union->min_z, $box->min_z),
				max($union->max_x, $box->max_x), max($union->max_y, $box->max_y), max($union->max_z, $box->max_z)
			);
		}
		return $union;
	}

	/**
	 * Writes the box as pixel bounds, the format of collision boxes since 1.21.130.
	 *
	 * @return CompoundTag the given compound.
	 */
	public function writeBounds(CompoundTag $nbt) : CompoundTag{
		return $nbt
			->setFloat("maxX", $this->max_x)
			->setFloat("maxY", $this->max_y)
			->setFloat("maxZ", $this->max_z)
			->setFloat("minX", $this->min_x)
			->setFloat("minY", $this->min_y)
			->setFloat("minZ", $this->min_z);
	}

	/**
	 * Writes the box as an origin relative to the bottom center of the block and a size, the format of
	 * selection boxes and of legacy collision boxes.
	 *
	 * @return CompoundTag the given compound.
	 */
	public function writeOriginAndSize(CompoundTag $nbt) : CompoundTag{
		return $nbt
			->setTag("origin", new ListTag([new FloatTag($this->min_x - 8.0), new FloatTag($this->min_y), new FloatTag($this->min_z - 8.0)], NBT::TAG_Float))
			->setTag("size", new ListTag([new FloatTag($this->max_x - $this->min_x), new FloatTag($this->max_y - $this->min_y), new FloatTag($this->max_z - $this->min_z)], NBT::TAG_Float));
	}
}
