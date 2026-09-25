<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component\value;

use InvalidArgumentException;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\CompoundTag;
use function in_array;

final class LiquidDetectionRule{

	public const LIQUID_TYPE_WATER = "water";

	public const ON_TOUCH_BLOCKING = "blocking";
	public const ON_TOUCH_BROKEN = "broken";
	public const ON_TOUCH_POPPED = "popped";
	public const ON_TOUCH_NO_REACTION = "no_reaction";

	private const ON_TOUCH = [self::ON_TOUCH_BLOCKING, self::ON_TOUCH_BROKEN, self::ON_TOUCH_POPPED, self::ON_TOUCH_NO_REACTION];

	/**
	 * @param bool $can_contain_liquid whether the block can be waterlogged.
	 * @param self::ON_TOUCH_* $on_liquid_touches how the block reacts to flowing water.
	 * @param list<int> $stops_liquid_flowing_from Facing constants of directions directions liquid cannot flow out of the block to.
	 * @param bool $use_liquid_clipping whether the collision box visually clips the liquid.
	 */
	public function __construct(
		readonly public bool $can_contain_liquid = false,
		readonly public string $on_liquid_touches = self::ON_TOUCH_BLOCKING,
		readonly public array $stops_liquid_flowing_from = [],
		readonly public bool $use_liquid_clipping = false
	){
		TypeValidator::validateChoice("Liquid touch reaction", $on_liquid_touches, self::ON_TOUCH);
		foreach($stops_liquid_flowing_from as $face){
			in_array($face, Facing::ALL, true) || throw new InvalidArgumentException("Liquid flow direction must be a " . Facing::class . " constant, got {$face}");
		}
	}

	public function toNbt() : CompoundTag{
		$stops = 0;
		foreach($this->stops_liquid_flowing_from as $face){
			$stops |= 1 << $face;
		}
		return CompoundTag::create()
			->setByte("canContainLiquid", $this->can_contain_liquid ? 1 : 0)
			->setString("liquidType", self::LIQUID_TYPE_WATER)
			->setString("onLiquidTouches", $this->on_liquid_touches)
			->setByte("stopsLiquidFromDirection", $stops)
			->setByte("use_liquid_clipping", $this->use_liquid_clipping ? 1 : 0);
	}
}
