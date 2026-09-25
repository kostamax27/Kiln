<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use pocketmine\nbt\tag\CompoundTag;

final class CustomComponentsComponent implements BlockComponent{

	public const NAME = "minecraft:custom_components";

	/**
	 * Clients only report some interactions with data-driven blocks when told to.
	 *
	 * @param bool $player_interact whether using the block is sent to the server.
	 * @param bool $player_placing whether placing the block is sent to the server before being predicted.
	 */
	public function __construct(
		readonly public bool $player_interact = true,
		readonly public bool $player_placing = false
	){}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()
			->setByte("hasPlayerInteract", $this->player_interact ? 1 : 0)
			->setByte("hasPlayerPlacing", $this->player_placing ? 1 : 0)
			->setByte("isV1Component", 1));
	}
}
