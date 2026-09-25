<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class CooldownComponent implements ItemComponent{

	public const NAME = "minecraft:cooldown";

	public const TYPE_USE = "use";
	public const TYPE_ATTACK = "attack";

	private const TYPES = [self::TYPE_USE, self::TYPE_ATTACK];

	/**
	 * @param string $category items sharing a category cool down together, e.g. "myplugin:ruby".
	 * @param float $duration cooldown in seconds.
	 * @param self::TYPE_* $type action that triggers the cooldown.
	 */
	public function __construct(
		readonly public string $category,
		readonly public float $duration,
		readonly public string $type = self::TYPE_USE
	){
		TypeValidator::validateTag("Cooldown category", $category);
		TypeValidator::validateFloat("Cooldown duration", $duration, 0.0);
		TypeValidator::validateChoice("Cooldown type", $type, self::TYPES);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id < ProtocolVersions::V1_20_10){
			return;
		}
		$nbt = CompoundTag::create()
			->setString("category", $this->category)
			->setFloat("duration", $this->duration);
		if($protocol_id >= ProtocolVersions::V1_21_130){
			$nbt->setString("type", $this->type);
		}
		$components->setTag(self::NAME, $nbt);
	}
}