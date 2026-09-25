<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class BundleInteractionComponent implements ItemComponent{

	public const NAME = "minecraft:bundle_interaction";

	/**
	 * Requires {@see StorageItemComponent}.
	 *
	 * @param int $num_viewable_slots slots shown in the tooltip, [1, 64].
	 */
	public function __construct(
		readonly public int $num_viewable_slots = 12
	){
		TypeValidator::validateInt("Bundle viewable slots", $num_viewable_slots, 1, 64);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_110){
			$components->setTag(self::NAME, CompoundTag::create()->setInt("num_viewable_slots", $this->num_viewable_slots));
		}
	}
}