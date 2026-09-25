<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use InvalidArgumentException;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class SwingSoundsComponent implements ItemComponent{

	public const NAME = "minecraft:swing_sounds";

	/**
	 * @param string|null $attack_hit sound event.
	 * @param string|null $attack_miss sound event.
	 * @param string|null $attack_critical_hit sound event.
	 */
	public function __construct(
		readonly public ?string $attack_hit = null,
		readonly public ?string $attack_miss = null,
		readonly public ?string $attack_critical_hit = null
	){
		$attack_hit !== null || $attack_miss !== null || $attack_critical_hit !== null || throw new InvalidArgumentException("Expected at least one swing sound");
		foreach(["hit" => $attack_hit, "miss" => $attack_miss, "critical hit" => $attack_critical_hit] as $kind => $sound){
			if($sound !== null){
				TypeValidator::validateName("Attack {$kind} sound", $sound);
			}
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id < ProtocolVersions::V1_21_130){
			return;
		}
		$nbt = CompoundTag::create();
		if($this->attack_critical_hit !== null){
			$nbt->setString("attack_critical_hit", $this->attack_critical_hit);
		}
		if($this->attack_hit !== null){
			$nbt->setString("attack_hit", $this->attack_hit);
		}
		if($this->attack_miss !== null){
			$nbt->setString("attack_miss", $this->attack_miss);
		}
		$components->setTag(self::NAME, $nbt);
	}
}
