<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\state;

use InvalidArgumentException;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use UnitEnum;
use function array_map;
use function preg_match;
use function strtolower;

final class StateValues{

	public const STRING_PATTERN = "/^[a-z0-9_]+$/";

	private function __construct(){
	}

	public static function enumName(UnitEnum $case) : string{
		$name = strtolower($case->name);
		preg_match(self::STRING_PATTERN, $name) === 1 || throw new InvalidArgumentException("Enum case " . $case::class . "::{$case->name} cannot be a block state value");
		return $name;
	}

	/**
	 * @param list<int> $facings
	 * @return list<string>
	 */
	public static function facingNames(array $facings) : array{
		return array_map(Facing::toString(...), $facings);
	}

	/**
	 * @param list<int> $axes
	 * @return list<string>
	 */
	public static function axisNames(array $axes) : array{
		return array_map(Axis::toString(...), $axes);
	}

	/**
	 * @return list<int>
	 */
	public static function facingsExcept(int $except) : array{
		$facings = [];
		foreach(Facing::ALL as $facing){
			if($facing !== $except){
				$facings[] = $facing;
			}
		}
		return $facings;
	}
}
