<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use pocketmine\nbt\tag\CompoundTag;

final class FireResistantComponent implements ItemComponent{

	public const NAME = "minecraft:fire_resistant";

	/**
	 * @param bool $value whether the dropped item survives fire and lava.
	 */
	public function __construct(
		readonly public bool $value = true
	){}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_130){
			$components->setTag(self::NAME, CompoundTag::create()->setByte("value", $this->value ? 1 : 0));
		}
	}
}
