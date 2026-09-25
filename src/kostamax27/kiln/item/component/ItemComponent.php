<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use pocketmine\nbt\tag\CompoundTag;

interface ItemComponent{

	/**
	 * Returns the name that identifies this component within an item definition.
	 */
	public function getName() : string;

	/**
	 * Writes this component into the network definition of an item.
	 *
	 * @param CompoundTag $components the "components" compound of the item definition.
	 * @param CompoundTag $properties the "item_properties" compound of the item definition.
	 * @param int $protocol_id protocol of the client the definition is written for.
	 */
	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void;
}
