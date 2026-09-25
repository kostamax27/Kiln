<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class ThrowableComponent implements ItemComponent{

	public const NAME = "minecraft:throwable";

	/**
	 * @param bool $do_swing_animation whether the throw plays the arm swing animation.
	 * @param float $launch_power_scale scale at which launch power grows while drawing.
	 * @param float $max_draw_duration seconds of drawing after which power stops growing, >= $min_draw_duration.
	 * @param float $max_launch_power upper bound of the launch power.
	 * @param float $min_draw_duration seconds the item must be drawn before it can be thrown.
	 * @param bool $scale_power_by_draw_duration whether launch power grows with draw time (trident behaviour).
	 */
	public function __construct(
		readonly public bool $do_swing_animation = false,
		readonly public float $launch_power_scale = 1.0,
		readonly public float $max_draw_duration = 0.0,
		readonly public float $max_launch_power = 1.0,
		readonly public float $min_draw_duration = 0.0,
		readonly public bool $scale_power_by_draw_duration = false
	){
		TypeValidator::validateFloat("Launch power scale", $launch_power_scale, 0.0);
		TypeValidator::validateFloat("Max launch power", $max_launch_power, 0.0);
		TypeValidator::validateFloat("Min draw duration", $min_draw_duration, 0.0);
		TypeValidator::validateFloat("Max draw duration", $max_draw_duration, $min_draw_duration);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_20_10){
			$components->setTag(self::NAME, CompoundTag::create()
				->setByte("do_swing_animation", $this->do_swing_animation ? 1 : 0)
				->setFloat("launch_power_scale", $this->launch_power_scale)
				->setFloat("max_draw_duration", $this->max_draw_duration)
				->setFloat("max_launch_power", $this->max_launch_power)
				->setFloat("min_draw_duration", $this->min_draw_duration)
				->setByte("scale_power_by_draw_duration", $this->scale_power_by_draw_duration ? 1 : 0));
		}
	}
}
