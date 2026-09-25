<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;

final class FoodComponent implements ItemComponent{

	public const NAME = "minecraft:food";

	/**
	 * @param int $nutrition hunger points restored.
	 * @param float $saturation_modifier saturation restored is nutrition * saturation_modifier * 2.
	 * @param bool $can_always_eat whether the item can be eaten with a full hunger bar.
	 * @param string|null $using_converts_to identifier of the item the food turns into once eaten.
	 */
	public function __construct(
		readonly public int $nutrition,
		readonly public float $saturation_modifier,
		readonly public bool $can_always_eat = false,
		readonly public ?string $using_converts_to = null
	){
		TypeValidator::validateInt("Nutrition", $nutrition, 0);
		TypeValidator::validateFloat("Saturation modifier", $saturation_modifier, 0.0);
		if($using_converts_to !== null){
			TypeValidator::validateIdentifier("Food using_converts_to", $using_converts_to);
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_20_30){
			$components->setTag(self::NAME, CompoundTag::create()
				->setByte("can_always_eat", $this->can_always_eat ? 1 : 0)
				->setInt("nutrition", $this->nutrition)
				->setFloat("saturation_modifier", $this->saturation_modifier)
				->setTag("using_converts_to", $this->using_converts_to !== null ? new StringTag($this->using_converts_to) : CompoundTag::create()));
		}
	}
}