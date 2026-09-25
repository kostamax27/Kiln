<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class GeometryComponent implements BlockComponent{

	public const NAME = "minecraft:geometry";

	public const FULL_BLOCK = "minecraft:geometry.full_block";
	public const CROSS = "minecraft:geometry.cross";

	/**
	 * Geometry and {@see MaterialInstancesComponent} must be used together.
	 *
	 * @param string $identifier geometry identifier from the resource pack, e.g. "geometry.myplugin.lamp".
	 * @param array<string, string> $bone_visibility visibility of bones as a Molang expression, e.g. "true" or "query.block_state('myplugin:open')".
	 * @param string|null $culling identifier of a culling definition from the resource pack.
	 * @param bool $uv_lock whether UVs of every bone keep their orientation when the block is rotated.
	 */
	public function __construct(
		readonly public string $identifier = self::FULL_BLOCK,
		readonly public array $bone_visibility = [],
		readonly public ?string $culling = null,
		readonly public bool $uv_lock = false
	){
		TypeValidator::validateTag("Geometry identifier", $identifier);
		foreach($bone_visibility as $bone => $visibility){
			TypeValidator::validateTag("Geometry bone name", $bone);
			TypeValidator::validateText("Geometry bone visibility expression", $visibility);
		}
		if($culling !== null){
			TypeValidator::validateTag("Geometry culling", $culling);
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$components->setTag(self::NAME, $this->encode());
	}

	/**
	 * Encodes the geometry description, which other components such as {@see ItemVisualComponent} embed.
	 */
	public function encode() : CompoundTag{
		$bone_visibility = CompoundTag::create();
		foreach($this->bone_visibility as $bone => $visibility){
			$bone_visibility->setString($bone, $visibility);
		}
		$nbt = CompoundTag::create()
			->setTag("bone_visibility", $bone_visibility)
			->setString("identifier", $this->identifier);
		if($this->culling !== null){
			$nbt->setString("culling", $this->culling);
		}
		if($this->uv_lock){
			$nbt->setByte("uv_lock", 1);
		}
		return $nbt;
	}
}
