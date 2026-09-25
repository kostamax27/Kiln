<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\property;

use pocketmine\nbt\tag\CompoundTag;

final class AllowOffHandProperty implements ItemProperty{

	public const NAME = "allow_off_hand";

	/**
	 * @param bool $value whether the item can be placed in the off-hand slot.
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
