<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class InteractButtonComponent implements ItemComponent{

	public const NAME = "minecraft:interact_button";

	public const DEFAULT_TEXT = "action.interact.use";

	/**
	 * @param string $text text on the touch-control interact button, literal or translation key.
	 */
	public function __construct(
		readonly public string $text = self::DEFAULT_TEXT
	){
		TypeValidator::validateText("Interact button text", $text);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()
			->setString("interact_text", $this->text)
			->setByte("requires_interact", 1));
	}
}