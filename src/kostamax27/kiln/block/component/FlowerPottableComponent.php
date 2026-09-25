<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\network\ProtocolVersions;
use pocketmine\nbt\tag\CompoundTag;

final class FlowerPottableComponent implements BlockComponent{

	public const NAME = "minecraft:flower_pottable";

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_120){
			$components->setTag(self::NAME, CompoundTag::create());
		}
	}
}
