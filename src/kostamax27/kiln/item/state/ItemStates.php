<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\state;

use InvalidArgumentException;
use pocketmine\data\runtime\InvalidSerializedRuntimeDataException;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\data\runtime\RuntimeDataReader;
use pocketmine\data\runtime\RuntimeDataSizeCalculator;
use pocketmine\item\Item;
use ReflectionMethod;
use function count;

final class ItemStates{

	public const MAX_BITS = 16;
	public const MAX_VARIANTS = 256;

	private function __construct(){
	}

	/**
	 * Returns the item in every state its describeState() allows, keyed by state ID.
	 *
	 * @return non-empty-array<int, Item>
	 */
	public static function enumerate(Item $item) : array{
		$calculator = new RuntimeDataSizeCalculator();
		self::describe(clone $item, $calculator);
		$bits = $calculator->getBitsUsed();
		$bits <= self::MAX_BITS || throw new InvalidArgumentException("Item {$item->getName()} describes {$bits} bits of state, at most " . self::MAX_BITS . " are supported");

		$states = [];
		for($data = 0; $data < (1 << $bits); ++$data){
			$state = clone $item;
			try{
				self::describe($state, new RuntimeDataReader($bits, $data));
			}catch(InvalidSerializedRuntimeDataException){
				continue;
			}
			$states[$state->getStateId()] ??= $state;
			count($states) <= self::MAX_VARIANTS || throw new InvalidArgumentException("Item {$item->getName()} has more than " . self::MAX_VARIANTS . " states");
		}
		return count($states) > 0 ? $states : [$item->getStateId() => clone $item];
	}

	private static function describe(Item $item, RuntimeDataDescriber $describer) : void{
		(new ReflectionMethod($item, "describeState"))->invoke($item, $describer);
	}
}