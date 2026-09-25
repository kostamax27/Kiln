<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component\value;

use InvalidArgumentException;
use pocketmine\nbt\tag\CompoundTag;

final class HandRenderOffsets{

	/**
	 * @param RenderTransform|null $first_person transform seen by the holder, null to keep the default.
	 * @param RenderTransform|null $third_person transform seen by other players, null to keep the default.
	 */
	public function __construct(
		readonly public ?RenderTransform $first_person = null,
		readonly public ?RenderTransform $third_person = null
	){
		$first_person !== null || $third_person !== null || throw new InvalidArgumentException("Hand render offsets must set a first or a third person transform");
	}

	public function toNbt() : CompoundTag{
		$nbt = CompoundTag::create();
		if($this->first_person !== null){
			$nbt->setTag("first_person", $this->first_person->toNbt());
		}
		if($this->third_person !== null){
			$nbt->setTag("third_person", $this->third_person->toNbt());
		}
		return $nbt;
	}
}
