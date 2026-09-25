<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component\value;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class ShooterAmmunition{

	/**
	 * @param string $item identifier of the ammunition item, e.g. "minecraft:arrow".
	 * @param bool $use_offhand whether ammunition in the off hand is used.
	 * @param bool $search_inventory whether the whole inventory is searched for ammunition.
	 * @param bool $use_in_creative whether ammunition is required in creative mode.
	 */
	public function __construct(
		readonly public string $item,
		readonly public bool $use_offhand = false,
		readonly public bool $search_inventory = false,
		readonly public bool $use_in_creative = false
	){
		TypeValidator::validateIdentifier("Ammunition item", $item);
	}

	public function toNbt() : CompoundTag{
		return CompoundTag::create()
			->setString("item", $this->item)
			->setByte("search_inventory", $this->search_inventory ? 1 : 0)
			->setByte("use_in_creative", $this->use_in_creative ? 1 : 0)
			->setByte("use_offhand", $this->use_offhand ? 1 : 0);
	}
}