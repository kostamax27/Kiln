<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class LightEmissionComponent implements BlockComponent{

	public const NAME = "minecraft:light_emission";

	/**
	 * @param int $emission emitted light level, [0, 15].
	 */
	public function __construct(
		readonly public int $emission
	){
		TypeValidator::validateInt("Light emission", $emission, 0, 15);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()->setByte("emission", $this->emission));
	}
}