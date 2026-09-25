<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\property;

use pocketmine\nbt\tag\CompoundTag;

interface ItemProperty{

	/**
	 * Returns the key this property occupies in "item_properties".
	 */
	public function getName() : string;

	/**
	 * Writes this property into the "item_properties" compound of an item definition.
	 *
	 * @param int $protocol_id protocol of the client the definition is written for.
	 */
	public function write(CompoundTag $properties, int $protocol_id) : void;
}