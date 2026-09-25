<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component\value;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class DurabilityThreshold{

	public const PARTICLE_NONE = "none";
	public const SOUND_UNDEFINED = "undefined";

	/**
	 * @param int $durability effects are emitted once durability drops to or below this value.
	 * @param string $particle_type particle emitted once the threshold is met, e.g. "crit".
	 * @param string $sound_event sound event, e.g. \"record.cat\".
	 */
	public function __construct(
		readonly public int $durability,
		readonly public string $particle_type = self::PARTICLE_NONE,
		readonly public string $sound_event = self::SOUND_UNDEFINED
	){
		TypeValidator::validateInt("Durability threshold", $durability, 0);
		TypeValidator::validateName("Durability threshold particle type", $particle_type);
		TypeValidator::validateName("Durability threshold sound event", $sound_event);
	}

	public function toNbt() : CompoundTag{
		return CompoundTag::create()
			->setInt("durability", $this->durability)
			->setString("particle_type", $this->particle_type)
			->setString("sound_event", $this->sound_event);
	}
}