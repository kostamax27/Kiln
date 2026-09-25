<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\item\component\value\BlockDescriptor;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;

final class EntityPlacerComponent implements ItemComponent{

	public const NAME = "minecraft:entity_placer";

	/**
	 * @param string $entity identifier of the entity the client predicts being placed.
	 * @param list<BlockDescriptor> $use_on blocks the item may be used on, empty for any block.
	 * @param list<BlockDescriptor> $dispense_on blocks a dispenser may place the entity on, empty for any block.
	 */
	public function __construct(
		readonly public string $entity,
		readonly public array $use_on = [],
		readonly public array $dispense_on = []
	){
		TypeValidator::validateActorIdentifier("Entity placer entity", $entity);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_20_10){
			$components->setTag(self::NAME, CompoundTag::create()
				->setTag("dispense_on", new ListTag(array_map(static fn(BlockDescriptor $block) : CompoundTag => $block->toNbt(), $this->dispense_on), NBT::TAG_Compound))
				->setString("entity", $this->entity)
				->setTag("use_on", new ListTag(array_map(static fn(BlockDescriptor $block) : CompoundTag => $block->toNbt(), $this->use_on), NBT::TAG_Compound)));
		}
	}
}
