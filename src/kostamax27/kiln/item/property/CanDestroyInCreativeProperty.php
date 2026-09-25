<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\property;

use pocketmine\nbt\tag\CompoundTag;

final class CanDestroyInCreativeProperty implements ItemProperty{

	public const NAME = "can_destroy_in_creative";

	/**
	 * @param bool $value whether swinging the item breaks blocks in creative mode.
	 */
	public function __construct(
		readonly public bool $value = true
	){}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $properties, int $protocol_id) : void{
		$properties->setByte(self::NAME, $this->value ? 1 : 0);
	}
}
