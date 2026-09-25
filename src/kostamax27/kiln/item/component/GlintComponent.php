<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use pocketmine\nbt\tag\CompoundTag;

final class GlintComponent implements ItemComponent{

	public const NAME = "minecraft:glint";

	/**
	 * @param bool $value whether the item has the enchantment glint.
	 */
	public function __construct(
		readonly public bool $value = true
	){}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$properties->setByte("foil", $this->value ? 1 : 0);
		if($protocol_id >= ProtocolVersions::V1_21_60){
			$components->setByte("minecraft:foil", $this->value ? 1 : 0);
		}
	}
}