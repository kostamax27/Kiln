<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use InvalidArgumentException;
use kostamax27\kiln\block\component\value\MaterialInstance;
use kostamax27\kiln\network\ProtocolVersions;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\CompoundTag;
use function count;
use function preg_match;

final class MaterialInstancesComponent implements BlockComponent{

	public const NAME = "minecraft:material_instances";

	public const KEY_PATTERN = "/^(\\*|[a-z0-9_]+)$/";

	public const ALL_FACES = "*";
	public const UP = "up";
	public const DOWN = "down";
	public const NORTH = "north";
	public const SOUTH = "south";
	public const EAST = "east";
	public const WEST = "west";

	/**
	 * Uses one material for every face.
	 */
	public static function all(MaterialInstance $material) : self{
		return new self([self::ALL_FACES => $material]);
	}

	/**
	 * Uses a material per face, with self::ALL_FACES as the material of faces left out.
	 *
	 * @param MaterialInstance $default material of faces that are not listed.
	 * @param array<int, MaterialInstance> $faces materials keyed by Facing constant.
	 */
	public static function faces(MaterialInstance $default, array $faces) : self{
		$materials = [self::ALL_FACES => $default];
		foreach($faces as $face => $material){
			$materials[Facing::toString($face)] = $material;
		}
		return new self($materials);
	}

	/**
	 * Geometry and {@see GeometryComponent} must be used together.
	 *
	 * @param array<string, MaterialInstance> $materials keyed by face (self::ALL_FACES, self::UP…) or by a material name used by the geometry.
	 */
	public function __construct(
		readonly public array $materials
	){
		count($materials) > 0 || throw new InvalidArgumentException("Material instances must contain at least one entry");
		foreach($materials as $key => $_){
			preg_match(self::KEY_PATTERN, $key) === 1 || throw new InvalidArgumentException("Material instance key must be \"*\", a face name or a material name matching " . self::KEY_PATTERN . ", got \"{$key}\"");
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$components->setTag(self::NAME, $this->encode($protocol_id));
	}

	/**
	 * Encodes the material instances, which other components such as {@see ItemVisualComponent} embed.
	 */
	public function encode(int $protocol_id) : CompoundTag{
		$materials = CompoundTag::create();
		foreach($this->materials as $key => $material){
			$nbt = CompoundTag::create()
				->setByte("isotropic", $material->isotropic ? 1 : 0)
				->setString("render_method", $material->render_method)
				->setString("texture", $material->texture)
				->setString("tint_method", $material->tint_method);
			if($protocol_id >= ProtocolVersions::V1_26_20){
				$nbt->setFloat("ambient_occlusion", $material->ambient_occlusion);
			}else{
				$nbt->setByte("ambient_occlusion", $material->ambient_occlusion > 0.0 ? 1 : 0);
			}
			if($protocol_id >= ProtocolVersions::V1_21_60){
				$nbt->setByte("packed_bools", $material->face_dimming ? 1 : 0);
			}else{
				$nbt->setByte("face_dimming", $material->face_dimming ? 1 : 0);
			}
			$materials->setTag($key, $nbt);
		}
		return CompoundTag::create()
			->setTag("mappings", CompoundTag::create())
			->setTag("materials", $materials);
	}
}
