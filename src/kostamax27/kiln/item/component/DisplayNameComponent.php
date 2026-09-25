<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class DisplayNameComponent implements ItemComponent{

	public const NAME = "minecraft:display_name";

	/**
	 * @param string $value literal name or a translation key from the resource pack.
	 */
	public function __construct(
		readonly public string $value
	){
		TypeValidator::validateText("Display name", $value);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()->setString("value", $this->value));
	}
}
