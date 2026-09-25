<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use InvalidArgumentException;
use kostamax27\kiln\item\component\value\HandRenderOffsets;
use kostamax27\kiln\item\component\value\RenderTransform;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use function round;

final class RenderOffsetsComponent implements ItemComponent{

	public const NAME = "minecraft:render_offsets";

	public const DEFAULT_TEXTURE_SIZE = 16;

	/**
	 * Scales an item whose texture is larger than 16x16 so that it is held at the size of a regular item.
	 *
	 * @param int $width texture width in pixels, [16, 1024].
	 * @param int $height texture height in pixels, [16, 1024].
	 */
	public static function forTextureSize(int $width, int $height) : self{
		TypeValidator::validateInt("Texture width", $width, self::DEFAULT_TEXTURE_SIZE, 1024);
		TypeValidator::validateInt("Texture height", $height, self::DEFAULT_TEXTURE_SIZE, 1024);
		$x = self::DEFAULT_TEXTURE_SIZE / $width;
		$y = self::DEFAULT_TEXTURE_SIZE / $height;
		$off_hand = new RenderTransform(scale: self::scale(0.065 * $x, 0.25 * $y));
		return new self(
			new HandRenderOffsets(new RenderTransform(scale: self::scale(0.039 * $x, 0.039 * $y)), new RenderTransform(scale: self::scale(0.1 * $x, 0.1 * $y))),
			new HandRenderOffsets($off_hand, $off_hand)
		);
	}

	private static function scale(float $horizontal, float $vertical) : Vector3{
		return new Vector3(round($horizontal, 8), round($vertical, 8), round($horizontal, 8));
	}

	/**
	 * Offsets the way the item is rendered in each hand.
	 */
	public function __construct(
		readonly public ?HandRenderOffsets $main_hand = null,
		readonly public ?HandRenderOffsets $off_hand = null
	){
		$main_hand !== null || $off_hand !== null || throw new InvalidArgumentException("Render offsets must set the main hand or the off hand");
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$nbt = CompoundTag::create();
		if($this->main_hand !== null){
			$nbt->setTag("main_hand", $this->main_hand->toNbt());
		}
		if($this->off_hand !== null){
			$nbt->setTag("off_hand", $this->off_hand->toNbt());
		}
		$components->setTag(self::NAME, $nbt);
	}
}