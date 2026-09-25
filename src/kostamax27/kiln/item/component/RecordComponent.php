<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class RecordComponent implements ItemComponent{

	public const NAME = "minecraft:record";

	/**
	 * @param string $sound_event sound event, e.g. \"record.cat\".
	 * @param float $duration length of the sound in seconds.
	 * @param int $comparator_signal comparator signal strength while playing, [1, 13].
	 */
	public function __construct(
		readonly public string $sound_event,
		readonly public float $duration,
		readonly public int $comparator_signal = 1
	){
		TypeValidator::validateName("Record sound event", $sound_event);
		TypeValidator::validateFloat("Record duration", $duration, 0.0);
		TypeValidator::validateInt("Record comparator signal", $comparator_signal, 1, 13);
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_20_10){
			$components->setTag(self::NAME, CompoundTag::create()
				->setInt("comparator_signal", $this->comparator_signal)
				->setFloat("duration", $this->duration)
				->setString("sound_event", $this->sound_event));
		}
	}
}
