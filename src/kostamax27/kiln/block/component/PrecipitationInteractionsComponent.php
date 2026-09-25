<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class PrecipitationInteractionsComponent implements BlockComponent{

	public const NAME = "minecraft:precipitation_interactions";

	public const BEHAVIOR_OBSTRUCT_RAIN_ACCUMULATE_SNOW = "obstruct_rain_accumulate_snow";
	public const BEHAVIOR_OBSTRUCT_RAIN = "obstruct_rain";
	public const BEHAVIOR_NONE = "none";

	private const BEHAVIORS = [self::BEHAVIOR_OBSTRUCT_RAIN_ACCUMULATE_SNOW, self::BEHAVIOR_OBSTRUCT_RAIN, self::BEHAVIOR_NONE];

	/**
	 * @param self::BEHAVIOR_* $precipitation_behavior
	 */
	public function __construct(
		readonly public string $precipitation_behavior
	){
		TypeValidator::validateChoice("Precipitation behavior", $precipitation_behavior, self::BEHAVIORS);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_21_120){
			$components->setTag(self::NAME, CompoundTag::create()->setString("precipitation_behavior", $this->precipitation_behavior));
		}
	}
}
