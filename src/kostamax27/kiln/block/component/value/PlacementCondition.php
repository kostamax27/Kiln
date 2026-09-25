<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component\value;

use InvalidArgumentException;
use kostamax27\kiln\item\component\value\BlockDescriptor;
use pocketmine\math\Facing;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;
use function in_array;

final class PlacementCondition{

	/**
	 * @param list<int> $allowed_faces Facing constants of faces of the adjacent block this block may be placed against.
	 * @param list<BlockDescriptor> $block_filters blocks this block may be placed against, empty for any block.
	 */
	public function __construct(
		readonly public array $allowed_faces = Facing::ALL,
		readonly public array $block_filters = []
	){
		foreach($allowed_faces as $face){
			in_array($face, Facing::ALL, true) || throw new InvalidArgumentException("Allowed face must be a " . Facing::class . " constant, got {$face}");
		}
	}

	public function toNbt() : CompoundTag{
		$allowed_faces = 0;
		foreach($this->allowed_faces as $face){
			$allowed_faces |= 1 << $face;
		}
		return CompoundTag::create()
			->setByte("allowed_faces", $allowed_faces)
			->setTag("block_filters", new ListTag(array_map(static function(BlockDescriptor $descriptor) : CompoundTag{
				$nbt = $descriptor->toNbt();
				if($nbt->getTag("tags") !== null){
					$nbt->setInt("tags_version", 6);
				}
				return $nbt;
			}, $this->block_filters), NBT::TAG_Compound));
	}
}