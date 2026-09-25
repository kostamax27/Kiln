<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class DamageComponent implements ItemComponent{

	public const NAME = "minecraft:damage";

	/**
	 * @param int $value attack damage shown by the client, [0, 32767].
	 */
	public function __construct(
		readonly public int $value
	){
		TypeValidator::validateInt("Damage", $value, 0, 32767);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$properties->setInt("damage", $this->value);
		if($protocol_id >= ProtocolVersions::V1_21_130){
			$components->setTag(self::NAME, CompoundTag::create()->setShort("value", $this->value));
		}
	}
}
