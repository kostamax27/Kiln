<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class HoverTextColorComponent implements ItemComponent{

	public const NAME = "minecraft:hover_text_color";

	public const COLOR_BLACK = "black";
	public const COLOR_DARK_BLUE = "dark_blue";
	public const COLOR_DARK_GREEN = "dark_green";
	public const COLOR_DARK_AQUA = "dark_aqua";
	public const COLOR_DARK_RED = "dark_red";
	public const COLOR_DARK_PURPLE = "dark_purple";
	public const COLOR_GOLD = "gold";
	public const COLOR_GRAY = "gray";
	public const COLOR_DARK_GRAY = "dark_gray";
	public const COLOR_BLUE = "blue";
	public const COLOR_GREEN = "green";
	public const COLOR_AQUA = "aqua";
	public const COLOR_RED = "red";
	public const COLOR_LIGHT_PURPLE = "light_purple";
	public const COLOR_YELLOW = "yellow";
	public const COLOR_WHITE = "white";
	public const COLOR_MINECOIN_GOLD = "minecoin_gold";
	public const COLOR_MATERIAL_QUARTZ = "material_quartz";
	public const COLOR_MATERIAL_IRON = "material_iron";
	public const COLOR_MATERIAL_NETHERITE = "material_netherite";
	public const COLOR_MATERIAL_REDSTONE = "material_redstone";
	public const COLOR_MATERIAL_COPPER = "material_copper";
	public const COLOR_MATERIAL_GOLD = "material_gold";
	public const COLOR_MATERIAL_EMERALD = "material_emerald";
	public const COLOR_MATERIAL_DIAMOND = "material_diamond";
	public const COLOR_MATERIAL_LAPIS = "material_lapis";
	public const COLOR_MATERIAL_AMETHYST = "material_amethyst";
	public const COLOR_MATERIAL_RESIN = "material_resin";

	private const COLORS = [self::COLOR_BLACK, self::COLOR_DARK_BLUE, self::COLOR_DARK_GREEN, self::COLOR_DARK_AQUA, self::COLOR_DARK_RED, self::COLOR_DARK_PURPLE, self::COLOR_GOLD, self::COLOR_GRAY, self::COLOR_DARK_GRAY, self::COLOR_BLUE, self::COLOR_GREEN, self::COLOR_AQUA, self::COLOR_RED, self::COLOR_LIGHT_PURPLE, self::COLOR_YELLOW, self::COLOR_WHITE, self::COLOR_MINECOIN_GOLD, self::COLOR_MATERIAL_QUARTZ, self::COLOR_MATERIAL_IRON, self::COLOR_MATERIAL_NETHERITE, self::COLOR_MATERIAL_REDSTONE, self::COLOR_MATERIAL_COPPER, self::COLOR_MATERIAL_GOLD, self::COLOR_MATERIAL_EMERALD, self::COLOR_MATERIAL_DIAMOND, self::COLOR_MATERIAL_LAPIS, self::COLOR_MATERIAL_AMETHYST, self::COLOR_MATERIAL_RESIN];

	/**
	 * @param self::COLOR_* $value
	 */
	public function __construct(
		readonly public string $value
	){
		TypeValidator::validateChoice("Hover text color", $value, self::COLORS);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$properties->setString("hover_text_color", $this->value);
		if($protocol_id >= ProtocolVersions::V1_20_10){
			$components->setTag(self::NAME, CompoundTag::create()->setString("value", $this->value));
		}
	}
}
