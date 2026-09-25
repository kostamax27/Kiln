<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class IconComponent implements ItemComponent{

	public const NAME = "minecraft:icon";

	/**
	 * @param string $texture key of the texture in resource_pack/textures/item_texture.json.
	 * @param string|null $dyed texture shown once the item is dyed.
	 * @param string|null $trim armor trim palette texture.
	 */
	public function __construct(
		readonly public string $texture,
		readonly public ?string $dyed = null,
		readonly public ?string $trim = null
	){
		TypeValidator::validateTexture("Icon texture", $texture);
		if($dyed !== null){
			TypeValidator::validateTexture("Icon dyed texture", $dyed);
		}
		if($trim !== null){
			TypeValidator::validateTexture("Icon trim texture", $trim);
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_20_60){
			$textures = CompoundTag::create()->setString("default", $this->texture);
			if($this->dyed !== null){
				$textures->setString("dyed", $this->dyed);
			}
			if($this->trim !== null){
				$textures->setString("icon_trim", $this->trim);
			}
			$properties->setTag(self::NAME, CompoundTag::create()->setTag("textures", $textures));
		}else{
			$properties->setTag(self::NAME, CompoundTag::create()->setString("texture", $this->texture));
		}
	}
}