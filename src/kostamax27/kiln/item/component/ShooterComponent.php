<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\item\component\value\ShooterAmmunition;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;

final class ShooterComponent implements ItemComponent{

	public const NAME = "minecraft:shooter";

	/**
	 * Bow or crossbow-like drawing.
	 *
	 * @param non-empty-list<ShooterAmmunition> $ammunition
	 * @param bool $charge_on_draw whether charging starts when drawing begins (crossbow behaviour).
	 * @param float $max_draw_duration seconds after which drawing reaches full power.
	 * @param bool $scale_power_by_draw_duration whether launch power scales with draw time.
	 */
	public function __construct(
		readonly public array $ammunition,
		readonly public bool $charge_on_draw = false,
		readonly public float $max_draw_duration = 0.0,
		readonly public bool $scale_power_by_draw_duration = false
	){
		TypeValidator::validateNonEmptyList("Shooter ammunition", $ammunition);
		TypeValidator::validateFloat("Shooter max draw duration", $max_draw_duration, 0.0);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_20_10){
			$components->setTag(self::NAME, CompoundTag::create()
				->setTag("ammunition", new ListTag(array_map(static fn(ShooterAmmunition $ammunition) : CompoundTag => $ammunition->toNbt(), $this->ammunition), NBT::TAG_Compound))
				->setByte("charge_on_draw", $this->charge_on_draw ? 1 : 0)
				->setFloat("max_draw_duration", $this->max_draw_duration)
				->setByte("scale_power_by_draw_duration", $this->scale_power_by_draw_duration ? 1 : 0));
		}
	}
}