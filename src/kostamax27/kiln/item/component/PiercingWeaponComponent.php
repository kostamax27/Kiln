<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\item\component\value\FloatRange;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class PiercingWeaponComponent implements ItemComponent{

	public const NAME = "minecraft:piercing_weapon";

	/**
	 * Makes attacks hit every entity along the view vector within reach.
	 *
	 * @param FloatRange $reach range along the view vector in which entities are hit.
	 * @param FloatRange|null $creative_reach reach in creative mode, null to use $reach.
	 * @param float $hitbox_margin tolerance added to the raycast.
	 */
	public function __construct(
		readonly public FloatRange $reach,
		readonly public ?FloatRange $creative_reach = null,
		readonly public float $hitbox_margin = 0.0
	){
		TypeValidator::validateFloat("Hitbox margin", $hitbox_margin, 0.0);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_130){
			$components->setTag(self::NAME, CompoundTag::create()
				->setTag("creative_reach", ($this->creative_reach ?? $this->reach)->toNbt())
				->setFloat("hitbox_margin", $this->hitbox_margin)
				->setTag("reach", $this->reach->toNbt()));
		}
	}
}
