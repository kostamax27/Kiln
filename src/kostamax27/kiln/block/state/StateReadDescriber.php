<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\state;

use BadMethodCallException;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\runtime\LegacyRuntimeEnumDescriberTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use UnitEnum;
use function array_search;
use function is_int;
use function spl_object_id;

final class StateReadDescriber implements RuntimeDataDescriber{
	use LegacyRuntimeEnumDescriberTrait;
	use StateNamesTrait;

	/**
	 * @param list<string> $names
	 */
	public function __construct(
		readonly private BlockStateReader $in,
		array $names
	){
		$this->names = $names;
	}

	public function int(int $bits, int &$value) : void{
		$value = $this->in->readBoundedInt($this->nextName(), 0, (1 << $bits) - 1);
	}

	public function boundedInt(int $bits, int $min, int $max, int &$value) : void{
		$value = $this->in->readBoundedInt($this->nextName(), $min, $max);
	}

	public function boundedIntAuto(int $min, int $max, int &$value) : void{
		$value = $this->in->readBoundedInt($this->nextName(), $min, $max);
	}

	public function bool(bool &$value) : void{
		$value = $this->in->readBool($this->nextName());
	}

	public function horizontalFacing(int &$facing) : void{
		$facing = $this->readChoice($this->nextName(), Facing::HORIZONTAL, StateValues::facingNames(Facing::HORIZONTAL));
	}

	public function facingFlags(array &$faces) : void{
		$name = $this->nextName();
		$faces = [];
		foreach(Facing::ALL as $facing){
			if($this->in->readBool($name . "_" . Facing::toString($facing))){
				$faces[$facing] = $facing;
			}
		}
	}

	public function horizontalFacingFlags(array &$faces) : void{
		$name = $this->nextName();
		$faces = [];
		foreach(Facing::HORIZONTAL as $facing){
			if($this->in->readBool($name . "_" . Facing::toString($facing))){
				$faces[$facing] = $facing;
			}
		}
	}

	public function facing(int &$facing) : void{
		$facing = $this->readChoice($this->nextName(), Facing::ALL, StateValues::facingNames(Facing::ALL));
	}

	public function facingExcept(int &$facing, int $except) : void{
		$facings = StateValues::facingsExcept($except);
		$facing = $this->readChoice($this->nextName(), $facings, StateValues::facingNames($facings));
	}

	public function axis(int &$axis) : void{
		$axes = [Axis::Y, Axis::Z, Axis::X];
		$axis = $this->readChoice($this->nextName(), $axes, StateValues::axisNames($axes));
	}

	public function horizontalAxis(int &$axis) : void{
		$axes = [Axis::Z, Axis::X];
		$axis = $this->readChoice($this->nextName(), $axes, StateValues::axisNames($axes));
	}

	public function wallConnections(array &$connections) : void{
		throw new BadMethodCallException("wallConnections() is not supported by custom blocks");
	}

	public function brewingStandSlots(array &$slots) : void{
		throw new BadMethodCallException("brewingStandSlots() is not supported by custom blocks");
	}

	public function railShape(int &$railShape) : void{
		throw new BadMethodCallException("railShape() is not supported by custom blocks");
	}

	public function straightOnlyRailShape(int &$railShape) : void{
		throw new BadMethodCallException("straightOnlyRailShape() is not supported by custom blocks");
	}

	public function enum(UnitEnum &$case) : void{
		$name = $this->nextName();
		$value = $this->in->readString($name);
		foreach($case::cases() as $candidate){
			if(StateValues::enumName($candidate) === $value){
				$case = $candidate;
				return;
			}
		}
		throw $this->in->badValueException($name, $value);
	}

	public function enumSet(array &$set, array $allCases) : void{
		$name = $this->nextName();
		$set = [];
		foreach($allCases as $case){
			if($this->in->readBool($name . "_" . StateValues::enumName($case))){
				$set[spl_object_id($case)] = $case;
			}
		}
	}

	/**
	 * @param list<int> $choices
	 * @param list<string> $names
	 */
	private function readChoice(string $name, array $choices, array $names) : int{
		$value = $this->in->readString($name);
		$index = array_search($value, $names, true);
		return is_int($index) ? $choices[$index] : throw $this->in->badValueException($name, $value);
	}
}