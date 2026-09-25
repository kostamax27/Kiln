<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\block\component\value\MaterialInstance;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class DestructionParticlesComponent implements BlockComponent{

	public const NAME = "minecraft:destruction_particles";

	/**
	 * @param string $texture texture key of the particles, empty to use the block texture.
	 * @param int $particle_count particles spawned when the block breaks, [0, 255].
	 * @param MaterialInstance::TINT_METHOD_* $tint_method
	 */
	public function __construct(
		readonly public string $texture = "",
		readonly public int $particle_count = 100,
		readonly public string $tint_method = MaterialInstance::TINT_METHOD_NONE
	){
		if($texture !== ""){
			TypeValidator::validateTexture("Destruction particle texture", $texture);
		}
		TypeValidator::validateInt("Destruction particle count", $particle_count, 0, 255);
		TypeValidator::validateChoice("Destruction particle tint method", $tint_method, MaterialInstance::TINT_METHODS);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_100){
			$components->setTag(self::NAME, CompoundTag::create()
				->setInt("particle_count", $this->particle_count)
				->setString("texture", $this->texture)
				->setString("tint_method", $this->tint_method));
		}
	}
}
