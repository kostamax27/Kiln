<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\network\ProtocolVersions;
use pocketmine\nbt\tag\CompoundTag;

final class ItemVisualComponent implements BlockComponent{

	public const NAME = "minecraft:item_visual";

	/**
	 * Renders the item of this block with its own geometry and materials instead of the block's.
	 */
	public function __construct(
		readonly public GeometryComponent $geometry,
		readonly public MaterialInstancesComponent $material_instances
	){}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_60){
			$components->setTag(self::NAME, CompoundTag::create()
				->setTag("geometryDescription", $this->geometry->encode())
				->setTag("materialInstancesDescription", $this->material_instances->encode($protocol_id)));
		}
	}
}
