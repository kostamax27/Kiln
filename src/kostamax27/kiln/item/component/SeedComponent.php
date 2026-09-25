<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use InvalidArgumentException;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\math\Facing;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use function array_map;
use function in_array;

final class SeedComponent implements ItemComponent{

	public const NAME = "minecraft:seed";

	/**
	 * @param string $crop_result identifier of the block placed when planting.
	 * @param list<string> $plant_at identifiers of blocks the seed can be planted on, empty for farmland.
	 * @param bool $plant_at_any_solid_surface deprecated by Mojang, still sent by bds for glow berries.
	 * @param int $plant_at_face Facing constant; deprecated by Mojang, still sent by bds.
	 */
	public function __construct(
		readonly public string $crop_result,
		readonly public array $plant_at = [],
		readonly public bool $plant_at_any_solid_surface = false,
		readonly public int $plant_at_face = Facing::UP
	){
		TypeValidator::validateIdentifier("Seed crop result", $crop_result);
		foreach($plant_at as $block){
			TypeValidator::validateIdentifier("Seed plant_at block", $block);
		}
		in_array($plant_at_face, Facing::ALL, true) || throw new InvalidArgumentException("Seed plant_at_face must be a " . Facing::class . " constant, got {$plant_at_face}");
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()
			->setString("crop_result", $this->crop_result)
			->setTag("plant_at", new ListTag(array_map(static fn(string $block) : StringTag => new StringTag($block), $this->plant_at), NBT::TAG_String))
			->setByte("plant_at_any_solid_surface", $this->plant_at_any_solid_surface ? 1 : 0)
			->setString("plant_at_face", Facing::toString($this->plant_at_face)));
	}
}
