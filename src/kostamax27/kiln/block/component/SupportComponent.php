<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class SupportComponent implements BlockComponent{

	public const NAME = "minecraft:support";

	public const SHAPE_STAIR = "stair";
	public const SHAPE_FENCE = "fence";

	private const SHAPES = [self::SHAPE_STAIR, self::SHAPE_FENCE];

	/**
	 * @param self::SHAPE_* $shape
	 */
	public function __construct(
		readonly public string $shape
	){
		TypeValidator::validateChoice("Support shape", $shape, self::SHAPES);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_26_0){
			$components->setTag(self::NAME, CompoundTag::create()->setString("shape", $this->shape));
		}
	}
}