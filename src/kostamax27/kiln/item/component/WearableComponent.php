<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class WearableComponent implements ItemComponent{

	public const NAME = "minecraft:wearable";

	public const SLOT_ARMOR_HEAD = "slot.armor.head";
	public const SLOT_ARMOR_CHEST = "slot.armor.chest";
	public const SLOT_ARMOR_LEGS = "slot.armor.legs";
	public const SLOT_ARMOR_FEET = "slot.armor.feet";
	public const SLOT_ARMOR_BODY = "slot.armor.body";
	public const SLOT_MAIN_HAND = "slot.weapon.mainhand";
	public const SLOT_OFF_HAND = "slot.weapon.offhand";

	private const SLOTS = [self::SLOT_ARMOR_HEAD, self::SLOT_ARMOR_CHEST, self::SLOT_ARMOR_LEGS, self::SLOT_ARMOR_FEET, self::SLOT_ARMOR_BODY, self::SLOT_MAIN_HAND, self::SLOT_OFF_HAND];

	/**
	 * @param self::SLOT_* $slot
	 * @param int $protection armor points shown on the client.
	 * @param bool $dispensable whether a dispenser can equip the item.
	 */
	public function __construct(
		readonly public string $slot,
		readonly public int $protection = 0,
		readonly public bool $dispensable = true
	){
		TypeValidator::validateChoice("Wearable slot", $slot, self::SLOTS);
		TypeValidator::validateInt("Wearable protection", $protection, 0);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_20_30){
			$components->setTag(self::NAME, CompoundTag::create()
				->setByte("dispensable", $this->dispensable ? 1 : 0)
				->setInt("protection", $this->protection)
				->setString("slot", $this->slot));
		}
	}
}
