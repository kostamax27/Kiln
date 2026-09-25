<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class StorageWeightLimitComponent implements ItemComponent{

	public const NAME = "minecraft:storage_weight_limit";

	/**
	 * @param int $max_weight_limit maximum summed weight of stored items, [0, 64].
	 */
	public function __construct(
		readonly public int $max_weight_limit = 64
	){
		TypeValidator::validateInt("Storage max weight limit", $max_weight_limit, 0, 64);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_110){
			$components->setTag(self::NAME, CompoundTag::create()->setInt("max_weight_limit", $this->max_weight_limit));
		}
	}
}