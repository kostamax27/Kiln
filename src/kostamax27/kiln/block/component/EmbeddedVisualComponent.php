<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\network\ProtocolVersions;
use pocketmine\nbt\tag\CompoundTag;

final class EmbeddedVisualComponent implements BlockComponent{

	public const NAME = "minecraft:embedded_visual";

	/**
	 * Renders the block with its own geometry and materials while embedded in another block, e.g. placed
	 * in a flower pot.
	 */
	public function __construct(
		readonly public GeometryComponent $geometry,
		readonly public MaterialInstancesComponent $material_instances
	){}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_120){
			$components->setTag(self::NAME, CompoundTag::create()
				->setTag("geometryDescription", $this->geometry->encode())
				->setTag("materialInstancesDescription", $this->material_instances->encode($protocol_id)));
		}
	}
}
