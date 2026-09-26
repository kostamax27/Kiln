<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\block\component\value\Box;
use pocketmine\nbt\tag\CompoundTag;

final class SelectionBoxComponent implements BlockComponent{

	public const NAME = "minecraft:selection_box";

	/**
	 * @param Box|null $box outline shown when looking at the block, null to disable selection.
	 */
	public function __construct(
		readonly public ?Box $box
	){}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$components->setTag(self::NAME, ($this->box?->writeOriginAndSize(CompoundTag::create()) ?? Box::writeEmptyOriginAndSize(CompoundTag::create()))->setByte("enabled", $this->box !== null ? 1 : 0));
	}
}
