<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use InvalidArgumentException;
use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\math\Facing;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use function array_map;
use function in_array;

final class ConnectionRuleComponent implements BlockComponent{

	public const NAME = "minecraft:connection_rule";

	public const ACCEPTS_ALL = "all";
	public const ACCEPTS_ONLY_FENCES = "only_fences";
	public const ACCEPTS_NONE = "none";

	private const ACCEPTS = [self::ACCEPTS_ALL, self::ACCEPTS_ONLY_FENCES, self::ACCEPTS_NONE];

	/**
	 * @param self::ACCEPTS_* $accepts_connections_from blocks allowed to connect to this block.
	 * @param list<int> $enabled_directions horizontal Facing constants.
	 */
	public function __construct(
		readonly public string $accepts_connections_from = self::ACCEPTS_ALL,
		readonly public array $enabled_directions = Facing::HORIZONTAL
	){
		TypeValidator::validateChoice("Accepted connections", $accepts_connections_from, self::ACCEPTS);
		foreach($enabled_directions as $direction){
			in_array($direction, Facing::HORIZONTAL, true) || throw new InvalidArgumentException("Connection direction must be a horizontal " . Facing::class . " constant, got {$direction}");
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		if($protocol_id >= ProtocolVersions::V1_26_0){
			$components->setTag(self::NAME, CompoundTag::create()
				->setString("accepts_connections_from", $this->accepts_connections_from)
				->setTag("enabled_directions", new ListTag(array_map(static fn(int $direction) : StringTag => new StringTag(Facing::toString($direction)), $this->enabled_directions), NBT::TAG_String)));
		}
	}
}