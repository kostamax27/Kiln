<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use pocketmine\nbt\tag\CompoundTag;

interface BlockComponent{

	/**
	 * Returns the name that identifies this component within a block definition or a permutation.
	 */
	public function getName() : string;

	/**
	 * Writes this component into the "components" compound of a block definition or of a permutation.
	 *
	 * @param int $protocol_id protocol of the client the definition is written for.
	 */
	public function write(CompoundTag $components, int $protocol_id) : void;
}
