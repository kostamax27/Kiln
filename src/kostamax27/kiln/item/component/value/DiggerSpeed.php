<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component\value;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class DiggerSpeed{

	/**
	 * @param BlockDescriptor $block blocks this speed applies to.
	 * @param int $speed digging speed, >= 0.
	 */
	public function __construct(
		readonly public BlockDescriptor $block,
		readonly public int $speed
	){
		TypeValidator::validateInt("Digger speed", $speed, 0);
	}

	public function toNbt() : CompoundTag{
		return CompoundTag::create()
			->setTag("block", $this->block->toNbt())
			->setInt("speed", $this->speed);
	}
}