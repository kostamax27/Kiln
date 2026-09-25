<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use InvalidArgumentException;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use function fmod;

final class TransformationComponent implements BlockComponent{

	public const NAME = "minecraft:transformation";

	/**
	 * @param Vector3 $rotation rotation in degrees around each axis, multiples of 90.
	 * @param Vector3 $translation offset in blocks.
	 * @param Vector3 $rotation_pivot point in blocks the rotation is applied around.
	 * @param Vector3 $scale_pivot point in blocks the scale is applied around.
	 */
	public function __construct(
		readonly public Vector3 $rotation = new Vector3(0, 0, 0),
		readonly public Vector3 $scale = new Vector3(1, 1, 1),
		readonly public Vector3 $translation = new Vector3(0, 0, 0),
		readonly public Vector3 $rotation_pivot = new Vector3(0, 0, 0),
		readonly public Vector3 $scale_pivot = new Vector3(0, 0, 0)
	){
		foreach(["x" => $rotation->x, "y" => $rotation->y, "z" => $rotation->z] as $axis => $degrees){
			TypeValidator::validateFloat("Rotation {$axis}", $degrees);
			fmod($degrees, 90.0) === 0.0 || throw new InvalidArgumentException("Rotation {$axis} must be a multiple of 90 degrees, got {$degrees}");
		}
		foreach([$scale, $translation, $rotation_pivot, $scale_pivot] as $vector){
			TypeValidator::validateFloat("Transformation component", $vector->x);
			TypeValidator::validateFloat("Transformation component", $vector->y);
			TypeValidator::validateFloat("Transformation component", $vector->z);
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()
			->setInt("RX", self::quarterTurns((float) $this->rotation->x))
			->setFloat("RXP", $this->rotation_pivot->x)
			->setInt("RY", self::quarterTurns((float) $this->rotation->y))
			->setFloat("RYP", $this->rotation_pivot->y)
			->setInt("RZ", self::quarterTurns((float) $this->rotation->z))
			->setFloat("RZP", $this->rotation_pivot->z)
			->setFloat("SX", $this->scale->x)
			->setFloat("SXP", $this->scale_pivot->x)
			->setFloat("SY", $this->scale->y)
			->setFloat("SYP", $this->scale_pivot->y)
			->setFloat("SZ", $this->scale->z)
			->setFloat("SZP", $this->scale_pivot->z)
			->setFloat("TX", $this->translation->x)
			->setFloat("TY", $this->translation->y)
			->setFloat("TZ", $this->translation->z));
	}

	private static function quarterTurns(float $degrees) : int{
		return (((int) ($degrees / 90)) % 4 + 4) % 4;
	}
}
