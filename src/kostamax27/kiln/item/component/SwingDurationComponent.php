<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class SwingDurationComponent implements ItemComponent{

	public const NAME = "minecraft:swing_duration";

	/**
	 * @param float $value seconds the swing animation takes; visual only.
	 */
	public function __construct(
		readonly public float $value = 0.3
	){
		TypeValidator::validateFloat("Swing duration", $value, 0.0);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_120){
			$components->setTag(self::NAME, CompoundTag::create()->setFloat("value", $this->value));
		}
	}
}
