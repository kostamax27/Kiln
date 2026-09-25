<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use pocketmine\nbt\tag\CompoundTag;

final class StackedByDataComponent implements ItemComponent{

	public const NAME = "minecraft:stacked_by_data";

	/**
	 * @param bool $value whether stacks of this item with different meta values stay separate.
	 */
	public function __construct(
		readonly public bool $value = true
	){}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$properties->setByte("stacked_by_data", $this->value ? 1 : 0);
		if($protocol_id >= ProtocolVersions::V1_21_60){
			$components->setByte(self::NAME, $this->value ? 1 : 0);
		}
	}
}
