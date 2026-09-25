<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\block\component\value\RandomOffsetAxis;
use kostamax27\kiln\network\ProtocolVersions;
use pocketmine\nbt\tag\CompoundTag;

final class RandomOffsetComponent implements BlockComponent{

	public const NAME = "minecraft:random_offset";

	/**
	 * Offsets the rendered block by a pseudo-random amount derived from its position, like vanilla grass
	 * and flowers.
	 */
	public function __construct(
		readonly public RandomOffsetAxis $x = new RandomOffsetAxis(),
		readonly public RandomOffsetAxis $y = new RandomOffsetAxis(),
		readonly public RandomOffsetAxis $z = new RandomOffsetAxis()
	){}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_100){
			$components->setTag(self::NAME, CompoundTag::create()
				->setTag("x", $this->x->toNbt())
				->setTag("y", $this->y->toNbt())
				->setTag("z", $this->z->toNbt()));
		}
	}
}
