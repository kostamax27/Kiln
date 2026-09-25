<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\property;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class FrameCountProperty implements ItemProperty{

	public const NAME = "frame_count";

	/**
	 * @param int $value number of animation frames of the item icon.
	 */
	public function __construct(
		readonly public int $value
	){
		TypeValidator::validateInt("Frame count", $value, 1);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $properties, int $protocol_id) : void{
		$properties->setInt(self::NAME, $this->value);
	}
}
