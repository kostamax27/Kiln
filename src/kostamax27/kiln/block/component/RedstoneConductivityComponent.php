<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\network\ProtocolVersions;
use pocketmine\nbt\tag\CompoundTag;

final class RedstoneConductivityComponent implements BlockComponent{

	public const NAME = "minecraft:redstone_conductivity";

	/**
	 * @param bool $redstone_conductor whether the block can be powered by redstone.
	 * @param bool $allows_wire_to_step_down whether redstone wire can step down over the block.
	 */
	public function __construct(
		readonly public bool $redstone_conductor = false,
		readonly public bool $allows_wire_to_step_down = true
	){}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_40){
			$components->setTag(self::NAME, CompoundTag::create()
				->setByte("allowsWireToStepDown", $this->allows_wire_to_step_down ? 1 : 0)
				->setByte("redstoneConductor", $this->redstone_conductor ? 1 : 0));
		}
	}
}