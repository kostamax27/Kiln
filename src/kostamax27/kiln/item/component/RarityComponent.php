<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class RarityComponent implements ItemComponent{

	public const NAME = "minecraft:rarity";

	public const RARITY_COMMON = "common";
	public const RARITY_UNCOMMON = "uncommon";
	public const RARITY_RARE = "rare";
	public const RARITY_EPIC = "epic";

	private const RARITIES = [self::RARITY_COMMON, self::RARITY_UNCOMMON, self::RARITY_RARE, self::RARITY_EPIC];

	/**
	 * @param self::RARITY_* $rarity
	 */
	public function __construct(
		readonly public string $rarity
	){
		TypeValidator::validateChoice("Rarity", $rarity, self::RARITIES);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_30){
			$components->setTag(self::NAME, CompoundTag::create()->setString("value", $this->rarity));
		}
	}
}