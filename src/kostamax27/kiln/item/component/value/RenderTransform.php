<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component\value;

use InvalidArgumentException;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;

final class RenderTransform{

	/**
	 * @param Vector3|null $position offset of the rendered item, null to keep the default.
	 * @param Vector3|null $rotation rotation in degrees, null to keep the default.
	 * @param Vector3|null $scale scale on each axis, null to keep the default.
	 */
	public function __construct(
		readonly public ?Vector3 $position = null,
		readonly public ?Vector3 $rotation = null,
		readonly public ?Vector3 $scale = null
	){
		$position !== null || $rotation !== null || $scale !== null || throw new InvalidArgumentException("Render transform must set a position, a rotation or a scale");
		foreach(["position" => $position, "rotation" => $rotation, "scale" => $scale] as $name => $vector){
			if($vector !== null){
				TypeValidator::validateFloat("Render {$name} x", (float) $vector->x);
				TypeValidator::validateFloat("Render {$name} y", (float) $vector->y);
				TypeValidator::validateFloat("Render {$name} z", (float) $vector->z);
			}
		}
	}

	public function toNbt() : CompoundTag{
		$nbt = CompoundTag::create();
		foreach(["position" => $this->position, "rotation" => $this->rotation, "scale" => $this->scale] as $name => $vector){
			if($vector !== null){
				$nbt->setTag($name, new ListTag([new FloatTag((float) $vector->x), new FloatTag((float) $vector->y), new FloatTag((float) $vector->z)], NBT::TAG_Float));
			}
		}
		return $nbt;
	}
}
