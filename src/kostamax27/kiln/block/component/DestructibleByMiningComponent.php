<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class DestructibleByMiningComponent implements BlockComponent{

	public const NAME = "minecraft:destructible_by_mining";

	/**
	 * @param float $seconds_to_destroy seconds to break the block by hand; equals PocketMine's hardness.
	 */
	public function __construct(
		readonly public float $seconds_to_destroy
	){
		TypeValidator::validateFloat("Seconds to destroy", $seconds_to_destroy, 0.0);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()->setFloat("value", $this->seconds_to_destroy));
	}
}