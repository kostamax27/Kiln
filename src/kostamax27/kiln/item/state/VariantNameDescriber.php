<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\state;

use InvalidArgumentException;
use kostamax27\kiln\block\state\StateValues;
use pocketmine\data\runtime\LegacyRuntimeEnumDescriberTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use UnitEnum;
use function array_map;
use function count;
use function implode;

final class VariantNameDescriber implements RuntimeDataDescriber{
	use LegacyRuntimeEnumDescriberTrait;

	/** @var list<string> */
	private array $parts = [];

	/**
	 * Returns the default variant name: the described values joined by underscores, e.g. "ruby".
	 */
	public function getName() : string{
		return implode("_", $this->parts);
	}

	public function int(int $bits, int &$value) : void{
		$this->parts[] = (string) $value;
	}

	public function boundedInt(int $bits, int $min, int $max, int &$value) : void{
		$this->parts[] = (string) $value;
	}

	public function boundedIntAuto(int $min, int $max, int &$value) : void{
		$this->parts[] = (string) $value;
	}

	public function bool(bool &$value) : void{
		$this->parts[] = $value ? "true" : "false";
	}

	public function horizontalFacing(int &$facing) : void{
		$this->parts[] = Facing::toString($facing);
	}

	public function facingFlags(array &$faces) : void{
		$this->parts[] = self::joinOrNone(array_map(Facing::toString(...), $faces));
	}

	public function horizontalFacingFlags(array &$faces) : void{
		$this->parts[] = self::joinOrNone(array_map(Facing::toString(...), $faces));
	}

	public function facing(int &$facing) : void{
		$this->parts[] = Facing::toString($facing);
	}

	public function facingExcept(int &$facing, int $except) : void{
		$this->parts[] = Facing::toString($facing);
	}

	public function axis(int &$axis) : void{
		$this->parts[] = Axis::toString($axis);
	}

	public function horizontalAxis(int &$axis) : void{
		$this->parts[] = Axis::toString($axis);
	}

	public function wallConnections(array &$connections) : void{
		throw self::unsupported("wallConnections");
	}

	public function brewingStandSlots(array &$slots) : void{
		throw self::unsupported("brewingStandSlots");
	}

	public function railShape(int &$railShape) : void{
		throw self::unsupported("railShape");
	}

	public function straightOnlyRailShape(int &$railShape) : void{
		throw self::unsupported("straightOnlyRailShape");
	}

	public function enum(UnitEnum &$case) : void{
		$this->parts[] = StateValues::enumName($case);
	}

	public function enumSet(array &$set, array $allCases) : void{
		$this->parts[] = self::joinOrNone(array_map(StateValues::enumName(...), $set));
	}

	/**
	 * @param array<array-key, string> $names
	 */
	private static function joinOrNone(array $names) : string{
		return count($names) > 0 ? implode("_", $names) : "none";
	}

	private static function unsupported(string $method) : InvalidArgumentException{
		return new InvalidArgumentException("Cannot derive a variant name from {$method}(), name variants with CustomItemBuilder::setVariantNames()");
	}
}
