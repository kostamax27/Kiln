<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class MaxStackSizeComponent implements ItemComponent{

	public const NAME = "minecraft:max_stack_size";

	/**
	 * @param int $value [1, 64].
	 */
	public function __construct(
		readonly public int $value
	){
		TypeValidator::validateInt("Max stack size", $value, 1, 64);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$properties->setInt("max_stack_size", $this->value);
		if($protocol_id >= ProtocolVersions::V1_21_60){
			$components->setInt(self::NAME, $this->value);
		}
	}
}