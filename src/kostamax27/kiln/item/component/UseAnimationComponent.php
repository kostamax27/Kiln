<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class UseAnimationComponent implements ItemComponent{

	public const NAME = "minecraft:use_animation";

	public const ANIMATION_NONE = "none";
	public const ANIMATION_EAT = "eat";
	public const ANIMATION_DRINK = "drink";
	public const ANIMATION_BLOCK = "block";
	public const ANIMATION_BOW = "bow";
	public const ANIMATION_CAMERA = "camera";
	public const ANIMATION_SPEAR = "spear";
	public const ANIMATION_CROSSBOW = "crossbow";
	public const ANIMATION_SPYGLASS = "spyglass";
	public const ANIMATION_BRUSH = "brush";

	private const ANIMATIONS = [self::ANIMATION_NONE, self::ANIMATION_EAT, self::ANIMATION_DRINK, self::ANIMATION_BLOCK, self::ANIMATION_BOW, self::ANIMATION_CAMERA, self::ANIMATION_SPEAR, self::ANIMATION_CROSSBOW, self::ANIMATION_SPYGLASS, self::ANIMATION_BRUSH];

	private const LEGACY_IDS = [
		self::ANIMATION_NONE => 0,
		self::ANIMATION_EAT => 1,
		self::ANIMATION_DRINK => 2,
		self::ANIMATION_BLOCK => 3,
		self::ANIMATION_BOW => 4,
		self::ANIMATION_CAMERA => 5,
		self::ANIMATION_SPEAR => 6,
		self::ANIMATION_CROSSBOW => 9,
		self::ANIMATION_SPYGLASS => 10,
		self::ANIMATION_BRUSH => 12
	];

	/**
	 * @param self::ANIMATION_* $animation
	 */
	public function __construct(
		readonly public string $animation
	){
		TypeValidator::validateChoice("Use animation", $animation, self::ANIMATIONS);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$properties->setInt("use_animation", self::LEGACY_IDS[$this->animation]);
		if($protocol_id >= ProtocolVersions::V1_21_60){
			$components->setTag(self::NAME, CompoundTag::create()->setString("value", $this->animation));
		}
	}
}