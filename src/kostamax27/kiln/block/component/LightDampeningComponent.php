<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class LightDampeningComponent implements BlockComponent{

	public const NAME = "minecraft:light_dampening";

	/**
	 * @param int $light_level light levels absorbed by the block, [0, 15].
	 */
	public function __construct(
		readonly public int $light_level = 15
	){
		TypeValidator::validateInt("Light dampening", $light_level, 0, 15);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()->setByte("lightLevel", $this->light_level));
	}
}