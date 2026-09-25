<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\block\component\value\LiquidDetectionRule;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;

final class LiquidDetectionComponent implements BlockComponent{

	public const NAME = "minecraft:liquid_detection";

	/**
	 * @param non-empty-list<LiquidDetectionRule> $rules
	 */
	public function __construct(
		readonly public array $rules
	){
		TypeValidator::validateNonEmptyList("Liquid detection rules", $rules);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_60){
			$components->setTag(self::NAME, CompoundTag::create()->setTag("detectionRules", new ListTag(array_map(static fn(LiquidDetectionRule $rule) : CompoundTag => $rule->toNbt(), $this->rules), NBT::TAG_Compound)));
		}
	}
}