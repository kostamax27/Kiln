<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class FrictionComponent implements BlockComponent{

	public const NAME = "minecraft:friction";

	/**
	 * @param float $value friction applied to entities on the block, [0, 0.9]; 1 - PocketMine's friction factor.
	 */
	public function __construct(
		readonly public float $value = 0.4
	){
		TypeValidator::validateFloat("Friction", $value, 0.0, 0.9);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()->setFloat("value", $this->value));
	}
}
