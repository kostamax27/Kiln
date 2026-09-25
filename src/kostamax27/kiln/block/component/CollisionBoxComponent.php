<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\block\component\value\Box;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;
use function count;

final class CollisionBoxComponent implements BlockComponent{

	public const NAME = "minecraft:collision_box";

	/**
	 * Creates a collision shape made of one or more boxes.
	 *
	 * @param non-empty-list<Box> $boxes
	 */
	public static function boxes(array $boxes) : self{
		TypeValidator::validateInt("Collision box count", count(TypeValidator::validateNonEmptyList("Collision boxes", $boxes)), 1, 16);
		return new self($boxes);
	}

	/**
	 * Disables collision, letting entities pass through the block.
	 */
	public static function disabled() : self{
		return new self([]);
	}

	/**
	 * @param list<Box> $boxes
	 */
	private function __construct(
		readonly public array $boxes
	){}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$enabled = count($this->boxes) > 0;
		if($protocol_id >= ProtocolVersions::V1_26_0){
			$nbt = CompoundTag::create()->setTag("boxes", new ListTag(array_map(static fn(Box $box) : CompoundTag => $box->writeBounds(CompoundTag::create()), $this->boxes), NBT::TAG_Compound));
		}else{
			$nbt = $enabled ? Box::union($this->boxes)->writeOriginAndSize(CompoundTag::create()) : CompoundTag::create();
		}
		$components->setTag(self::NAME, $nbt->setByte("enabled", $enabled ? 1 : 0));
	}
}