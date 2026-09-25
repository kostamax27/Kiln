<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class EnchantableComponent implements ItemComponent{

	public const NAME = "minecraft:enchantable";

	public const SLOT_NONE = "none";
	public const SLOT_ALL = "all";
	public const SLOT_GROUP_ARMOR = "g_armor";
	public const SLOT_ARMOR_HEAD = "armor_head";
	public const SLOT_ARMOR_TORSO = "armor_torso";
	public const SLOT_ARMOR_LEGS = "armor_legs";
	public const SLOT_ARMOR_FEET = "armor_feet";
	public const SLOT_GROUP_TOOL = "g_tool";
	public const SLOT_GROUP_DIGGING = "g_digging";
	public const SLOT_AXE = "axe";
	public const SLOT_BOW = "bow";
	public const SLOT_CARROT_ON_A_STICK = "carrot_stick";
	public const SLOT_COSMETIC_HEAD = "cosmetic_head";
	public const SLOT_CROSSBOW = "crossbow";
	public const SLOT_ELYTRA = "elytra";
	public const SLOT_FISHING_ROD = "fishing_rod";
	public const SLOT_FLINT_AND_STEEL = "flintsteel";
	public const SLOT_HOE = "hoe";
	public const SLOT_MELEE_SPEAR = "melee_spear";
	public const SLOT_PICKAXE = "pickaxe";
	public const SLOT_SHEARS = "shears";
	public const SLOT_SHIELD = "shield";
	public const SLOT_SHOVEL = "shovel";
	public const SLOT_SPEAR = "spear";
	public const SLOT_SWORD = "sword";

	private const SLOTS = [self::SLOT_NONE, self::SLOT_ALL, self::SLOT_GROUP_ARMOR, self::SLOT_ARMOR_HEAD, self::SLOT_ARMOR_TORSO, self::SLOT_ARMOR_LEGS, self::SLOT_ARMOR_FEET, self::SLOT_GROUP_TOOL, self::SLOT_GROUP_DIGGING, self::SLOT_AXE, self::SLOT_BOW, self::SLOT_CARROT_ON_A_STICK, self::SLOT_COSMETIC_HEAD, self::SLOT_CROSSBOW, self::SLOT_ELYTRA, self::SLOT_FISHING_ROD, self::SLOT_FLINT_AND_STEEL, self::SLOT_HOE, self::SLOT_MELEE_SPEAR, self::SLOT_PICKAXE, self::SLOT_SHEARS, self::SLOT_SHIELD, self::SLOT_SHOVEL, self::SLOT_SPEAR, self::SLOT_SWORD];

	/**
	 * @param self::SLOT_* $slot kind of enchantments that may be applied.
	 * @param int $value enchantability, [0, 127]; higher values yield better enchantments.
	 */
	public function __construct(
		readonly public string $slot,
		readonly public int $value
	){
		TypeValidator::validateChoice("Enchantable slot", $slot, self::SLOTS);
		TypeValidator::validateInt("Enchantability", $value, 0, 127);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$properties->setString("enchantable_slot", $this->slot);
		$properties->setInt("enchantable_value", $this->value);
		if($protocol_id >= ProtocolVersions::V1_21_130){
			$components->setTag(self::NAME, CompoundTag::create()
				->setString("slot", $this->slot)
				->setByte("value", $this->value));
		}
	}
}
