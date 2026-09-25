<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\item\component\value\FloatRange;
use kostamax27\kiln\item\component\value\KineticEffectConditions;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class KineticWeaponComponent implements ItemComponent{

	public const NAME = "minecraft:kinetic_weapon";

	/**
	 * Spear-like damage dealt by moving into targets.
	 *
	 * @param FloatRange $reach range along the view vector in which entities are hit.
	 * @param FloatRange|null $creative_reach reach in creative mode, null to use $reach.
	 * @param int $delay ticks after which kinetic effects start to apply, [0, 32767].
	 * @param float $hitbox_margin tolerance added to the raycast.
	 * @param float $damage_multiplier multiplier applied to the projected relative velocity.
	 * @param float $damage_modifier value added after applying the multiplier.
	 * @param KineticEffectConditions|null $damage_conditions conditions under which damage is dealt.
	 * @param KineticEffectConditions|null $knockback_conditions conditions under which targets are knocked back.
	 * @param KineticEffectConditions|null $dismount_conditions conditions under which riders are dismounted.
	 */
	public function __construct(
		readonly public FloatRange $reach,
		readonly public ?FloatRange $creative_reach = null,
		readonly public int $delay = 0,
		readonly public float $hitbox_margin = 0.0,
		readonly public float $damage_multiplier = 1.0,
		readonly public float $damage_modifier = 0.0,
		readonly public ?KineticEffectConditions $damage_conditions = null,
		readonly public ?KineticEffectConditions $knockback_conditions = null,
		readonly public ?KineticEffectConditions $dismount_conditions = null
	){
		TypeValidator::validateInt("Kinetic weapon delay", $delay, 0, 32767);
		TypeValidator::validateFloat("Kinetic weapon hitbox margin", $hitbox_margin, 0.0);
		TypeValidator::validateFloat("Kinetic weapon damage multiplier", $damage_multiplier);
		TypeValidator::validateFloat("Kinetic weapon damage modifier", $damage_modifier);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id < ProtocolVersions::V1_21_130){
			return;
		}
		$nbt = CompoundTag::create()
			->setTag("creative_reach", ($this->creative_reach ?? $this->reach)->toNbt())
			->setFloat("damage_modifier", $this->damage_modifier)
			->setFloat("damage_multiplier", $this->damage_multiplier)
			->setShort("delay", $this->delay)
			->setFloat("hitbox_margin", $this->hitbox_margin)
			->setTag("reach", $this->reach->toNbt());
		if($this->damage_conditions !== null){
			$nbt->setTag("damage_conditions", $this->damage_conditions->toNbt());
		}
		if($this->knockback_conditions !== null){
			$nbt->setTag("knockback_conditions", $this->knockback_conditions->toNbt());
		}
		if($this->dismount_conditions !== null){
			$nbt->setTag("dismount_conditions", $this->dismount_conditions->toNbt());
		}
		$components->setTag(self::NAME, CompoundTag::create()->setTag(self::NAME, $nbt));
	}
}
