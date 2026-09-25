<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\state;

use InvalidArgumentException;
use pocketmine\data\runtime\LegacyRuntimeEnumDescriberTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use UnitEnum;
use function array_map;
use function count;
use function range;

final class StateSchemaDescriber implements RuntimeDataDescriber{
	use LegacyRuntimeEnumDescriberTrait;

	/** @var list<BlockProperty> */
	private array $properties = [];

	private int $described = 0;

	/**
	 * @param list<string> $names
	 */
	public function __construct(
		readonly private array $names
	){
		foreach($names as $name){
			BlockProperty::validateName($name);
		}
	}

	/**
	 * @return list<BlockProperty>
	 */
	public function getProperties() : array{
		$this->described === count($this->names) || throw new InvalidArgumentException("Block describes {$this->described} state(s), but " . count($this->names) . " state name(s) were given; name every state described by describeBlockItemState() and describeBlockOnlyState(), in order");
		return $this->properties;
	}

	public function int(int $bits, int &$value) : void{
		$this->addInts(0, (1 << $bits) - 1);
	}

	public function boundedInt(int $bits, int $min, int $max, int &$value) : void{
		$this->addInts($min, $max);
	}

	public function boundedIntAuto(int $min, int $max, int &$value) : void{
		$this->addInts($min, $max);
	}

	public function bool(bool &$value) : void{
		$this->addBool($this->nextName());
	}

	public function horizontalFacing(int &$facing) : void{
		$this->addStrings(StateValues::facingNames(Facing::HORIZONTAL));
	}

	public function facingFlags(array &$faces) : void{
		$name = $this->nextName();
		if($name === ""){
			return;
		}
		foreach(Facing::ALL as $facing){
			$this->addBool($name . "_" . Facing::toString($facing));
		}
	}

	public function horizontalFacingFlags(array &$faces) : void{
		$name = $this->nextName();
		if($name === ""){
			return;
		}
		foreach(Facing::HORIZONTAL as $facing){
			$this->addBool($name . "_" . Facing::toString($facing));
		}
	}

	public function facing(int &$facing) : void{
		$this->addStrings(StateValues::facingNames(Facing::ALL));
	}

	public function facingExcept(int &$facing, int $except) : void{
		$this->addStrings(StateValues::facingNames(StateValues::facingsExcept($except)));
	}

	public function axis(int &$axis) : void{
		$this->addStrings(StateValues::axisNames([Axis::Y, Axis::Z, Axis::X]));
	}

	public function horizontalAxis(int &$axis) : void{
		$this->addStrings(StateValues::axisNames([Axis::Z, Axis::X]));
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
		$this->addStrings(array_map(StateValues::enumName(...), $case::cases()));
	}

	public function enumSet(array &$set, array $allCases) : void{
		$name = $this->nextName();
		if($name === ""){
			return;
		}
		foreach($allCases as $case){
			$this->addBool($name . "_" . StateValues::enumName($case));
		}
	}

	private function nextName() : string{
		return $this->names[$this->described++] ?? "";
	}

	private function addInts(int $min, int $max) : void{
		$name = $this->nextName();
		if($name !== ""){
			$this->properties[] = new BlockProperty($name, array_map(static fn(int $value) : IntTag => new IntTag($value), range($min, $max)));
		}
	}

	private function addBool(string $name) : void{
		if($name !== ""){
			$this->properties[] = new BlockProperty($name, [new ByteTag(0), new ByteTag(1)]);
		}
	}

	/**
	 * @param list<string> $values
	 */
	private function addStrings(array $values) : void{
		$name = $this->nextName();
		if($name !== ""){
			$tags = array_map(static fn(string $value) : StringTag => new StringTag($value), $values);
			count($tags) > 0 || throw new InvalidArgumentException("State \"{$name}\" has no possible values");
			$this->properties[] = new BlockProperty($name, $tags);
		}
	}

	private static function unsupported(string $method) : InvalidArgumentException{
		return new InvalidArgumentException("RuntimeDataDescriber::{$method}() describes a vanilla-specific state and cannot be used by custom blocks");
	}
}
