<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\property;

use pocketmine\nbt\tag\CompoundTag;

final class ShouldDespawnProperty implements ItemProperty{

	public const NAME = "should_despawn";

	/**
	 * @param bool $value whether the dropped item despawns after a while.
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