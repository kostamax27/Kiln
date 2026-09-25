<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class DyeableComponent implements ItemComponent{

	public const NAME = "minecraft:dyeable";

	/**
	 * Once dyed, the item shows the "dyed" texture of {@see IconComponent}.
	 *
	 * @param string $default_color hex color such as "#47ff5a".
	 */
	public function __construct(
		readonly public string $default_color = "#ffffff"
	){
		TypeValidator::validateHexColor("Dyeable default color", $default_color);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_30){
			$components->setTag(self::NAME, CompoundTag::create()->setString("default_color", $this->default_color));
		}
	}
}
