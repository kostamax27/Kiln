<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;
use function round;

final class UseModifiersComponent implements ItemComponent{

	public const NAME = "minecraft:use_modifiers";

	public const START_USING_ALWAYS = "always";
	public const START_USING_ON_ATTACK = "on_attack";

	private const START_USING = [self::START_USING_ALWAYS, self::START_USING_ON_ATTACK];

	/**
	 * @param float $use_duration time in seconds the item takes to use.
	 * @param float $movement_modifier multiplier applied to movement speed while using, [0, 1].
	 * @param bool $emit_vibrations whether starting and stopping use emits vibrations.
	 * @param self::START_USING_* $start_using when the item starts being used.
	 * @param string|null $start_sound sound event.
	 */
	public function __construct(
		readonly public float $use_duration,
		readonly public float $movement_modifier = 1.0,
		readonly public bool $emit_vibrations = true,
		readonly public string $start_using = self::START_USING_ALWAYS,
		readonly public ?string $start_sound = null
	){
		TypeValidator::validateFloat("Use duration", $use_duration, 0.0);
		TypeValidator::validateFloat("Movement modifier", $movement_modifier, 0.0, 1.0);
		TypeValidator::validateChoice("Use start trigger", $start_using, self::START_USING);
		if($start_sound !== null){
			TypeValidator::validateName("Use start sound", $start_sound);
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$properties->setInt("use_duration", (int) round($this->use_duration * 20));
		if($protocol_id < ProtocolVersions::V1_20_50){
			return;
		}
		$nbt = CompoundTag::create()
			->setFloat("movement_modifier", $this->movement_modifier)
			->setFloat("use_duration", $this->use_duration);
		if($protocol_id >= ProtocolVersions::V1_21_120){
			$nbt->setByte("emit_vibrations", $this->emit_vibrations ? 1 : 0);
		}
		if($this->start_sound !== null && $protocol_id >= ProtocolVersions::V1_21_130){
			$nbt->setString("start_sound", $this->start_sound);
		}
		if($protocol_id >= ProtocolVersions::V1_26_30){
			$nbt->setString("start_using", $this->start_using);
		}
		$components->setTag(self::NAME, $nbt);
	}
}