<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use function array_map;

final class DamageAbsorptionComponent implements ItemComponent{

	public const NAME = "minecraft:damage_absorption";

	public const CAUSE_ALL = "all";
	public const CAUSE_ANVIL = "anvil";
	public const CAUSE_BLOCK_EXPLOSION = "block_explosion";
	public const CAUSE_CAMPFIRE = "campfire";
	public const CAUSE_CHARGING = "charging";
	public const CAUSE_CONTACT = "contact";
	public const CAUSE_DROWNING = "drowning";
	public const CAUSE_ENTITY_ATTACK = "entity_attack";
	public const CAUSE_ENTITY_EXPLOSION = "entity_explosion";
	public const CAUSE_FALL = "fall";
	public const CAUSE_FALLING_BLOCK = "falling_block";
	public const CAUSE_FIRE = "fire";
	public const CAUSE_FIRE_TICK = "fire_tick";
	public const CAUSE_FIREWORKS = "fireworks";
	public const CAUSE_FLY_INTO_WALL = "fly_into_wall";
	public const CAUSE_FREEZING = "freezing";
	public const CAUSE_LAVA = "lava";
	public const CAUSE_LIGHTNING = "lightning";
	public const CAUSE_MACE_SMASH = "mace_smash";
	public const CAUSE_MAGIC = "magic";
	public const CAUSE_MAGMA = "magma";
	public const CAUSE_NONE = "none";
	public const CAUSE_OVERRIDE = "override";
	public const CAUSE_PISTON = "piston";
	public const CAUSE_PROJECTILE = "projectile";
	public const CAUSE_RAM_ATTACK = "ram_attack";
	public const CAUSE_SELF_DESTRUCT = "self_destruct";
	public const CAUSE_SONIC_BOOM = "sonic_boom";
	public const CAUSE_SOUL_CAMPFIRE = "soul_campfire";
	public const CAUSE_STALACTITE = "stalactite";
	public const CAUSE_STALAGMITE = "stalagmite";
	public const CAUSE_STARVE = "starve";
	public const CAUSE_SUFFOCATION = "suffocation";
	public const CAUSE_TEMPERATURE = "temperature";
	public const CAUSE_THORNS = "thorns";
	public const CAUSE_VOID = "void";
	public const CAUSE_WITHER = "wither";

	private const CAUSES = [self::CAUSE_ALL, self::CAUSE_ANVIL, self::CAUSE_BLOCK_EXPLOSION, self::CAUSE_CAMPFIRE, self::CAUSE_CHARGING, self::CAUSE_CONTACT, self::CAUSE_DROWNING, self::CAUSE_ENTITY_ATTACK, self::CAUSE_ENTITY_EXPLOSION, self::CAUSE_FALL, self::CAUSE_FALLING_BLOCK, self::CAUSE_FIRE, self::CAUSE_FIRE_TICK, self::CAUSE_FIREWORKS, self::CAUSE_FLY_INTO_WALL, self::CAUSE_FREEZING, self::CAUSE_LAVA, self::CAUSE_LIGHTNING, self::CAUSE_MACE_SMASH, self::CAUSE_MAGIC, self::CAUSE_MAGMA, self::CAUSE_NONE, self::CAUSE_OVERRIDE, self::CAUSE_PISTON, self::CAUSE_PROJECTILE, self::CAUSE_RAM_ATTACK, self::CAUSE_SELF_DESTRUCT, self::CAUSE_SONIC_BOOM, self::CAUSE_SOUL_CAMPFIRE, self::CAUSE_STALACTITE, self::CAUSE_STALAGMITE, self::CAUSE_STARVE, self::CAUSE_SUFFOCATION, self::CAUSE_TEMPERATURE, self::CAUSE_THORNS, self::CAUSE_VOID, self::CAUSE_WITHER];

	/**
	 * Requires {@see DurabilityComponent} and the item being worn in an armor slot.
	 *
	 * @param non-empty-list<self::CAUSE_*> $absorbable_causes
	 */
	public function __construct(
		readonly public array $absorbable_causes
	){
		foreach(TypeValidator::validateNonEmptyList("Absorbable causes", $absorbable_causes) as $cause){
			TypeValidator::validateChoice("Absorbable cause", $cause, self::CAUSES);
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_20){
			$components->setTag(self::NAME, CompoundTag::create()->setTag("absorbable_causes", new ListTag(array_map(static fn(string $cause) : StringTag => new StringTag($cause), $this->absorbable_causes), NBT::TAG_String)));
		}
	}
}
