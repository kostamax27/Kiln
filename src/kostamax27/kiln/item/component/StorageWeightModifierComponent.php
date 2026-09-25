<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class StorageWeightModifierComponent implements ItemComponent{

	public const NAME = "minecraft:storage_weight_modifier";

	/**
	 * @param int $weight_in_storage_item weight of this item inside a storage item, 0 to forbid storing it, [0, 64].
	 */
	public function __construct(
		readonly public int $weight_in_storage_item = 4
	){
		TypeValidator::validateInt("Storage weight", $weight_in_storage_item, 0, 64);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_110){
			$components->setTag(self::NAME, CompoundTag::create()->setInt("weight_in_storage_item", $this->weight_in_storage_item));
		}
	}
}