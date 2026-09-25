<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class ProjectileComponent implements ItemComponent{

	public const NAME = "minecraft:projectile";

	/**
	 * @param string $projectile_entity entity identifier spawned by the client, e.g. "minecraft:snowball".
	 * @param float $minimum_critical_power charge needed for a critical hit.
	 */
	public function __construct(
		readonly public string $projectile_entity,
		readonly public float $minimum_critical_power = 0.0
	){
		TypeValidator::validateActorIdentifier("Projectile entity", $projectile_entity);
		TypeValidator::validateFloat("Minimum critical power", $minimum_critical_power, 0.0);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_20_10){
			$components->setTag(self::NAME, CompoundTag::create()
				->setFloat("minimum_critical_power", $this->minimum_critical_power)
				->setString("projectile_entity", $this->projectile_entity));
		}
	}
}