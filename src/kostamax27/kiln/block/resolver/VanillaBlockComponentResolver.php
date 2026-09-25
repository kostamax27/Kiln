<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\resolver;

use kostamax27\kiln\block\component\DestructibleByMiningComponent;
use kostamax27\kiln\block\component\DisplayNameComponent;
use kostamax27\kiln\block\component\FrictionComponent;
use kostamax27\kiln\block\component\LightDampeningComponent;
use kostamax27\kiln\block\component\LightEmissionComponent;
use pocketmine\block\Block;
use function max;
use function min;

final class VanillaBlockComponentResolver implements BlockComponentResolver{

	public const UNBREAKABLE_SECONDS_TO_DESTROY = 1_000_000.0;

	private static self $instance;

	/**
	 * Returns the resolver that derives the display name, break time, friction and light of a block from
	 * its PocketMine definition.
	 */
	public static function instance() : self{
		return self::$instance ??= new self();
	}

	private function __construct(){
	}

	public function resolve(string $identifier, Block $block) : array{
		$hardness = $block->getBreakInfo()->getHardness();
		return [
			new DisplayNameComponent($block->getName()),
			new DestructibleByMiningComponent($hardness >= 0.0 ? $hardness : self::UNBREAKABLE_SECONDS_TO_DESTROY),
			new FrictionComponent(max(0.0, min(0.9, 1.0 - $block->getFrictionFactor()))),
			new LightEmissionComponent($block->getLightLevel()),
			new LightDampeningComponent($block->getLightFilter())
		];
	}
}
