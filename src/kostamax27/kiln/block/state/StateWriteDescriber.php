<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\state;

use BadMethodCallException;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\runtime\LegacyRuntimeEnumDescriberTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use UnitEnum;
use function spl_object_id;

final class StateWriteDescriber implements RuntimeDataDescriber{
	use LegacyRuntimeEnumDescriberTrait;
	use StateNamesTrait;

	/**
	 * @param list<string> $names
	 */
	public function __construct(
		readonly private BlockStateWriter $out,
		array $names
	){
		$this->names = $names;
	}

	public function int(int $bits, int &$value) : void{
		$this->out->writeInt($this->nextName(), $value);
	}

	public function boundedInt(int $bits, int $min, int $max, int &$value) : void{
		$this->out->writeInt($this->nextName(), $value);
	}

	public function boundedIntAuto(int $min, int $max, int &$value) : void{
		$this->out->writeInt($this->nextName(), $value);
	}

	public function bool(bool &$value) : void{
		$this->out->writeBool($this->nextName(), $value);
	}

	public function horizontalFacing(int &$facing) : void{
		$this->out->writeString($this->nextName(), Facing::toString($facing));
	}

	public function facingFlags(array &$faces) : void{
		$name = $this->nextName();
		foreach(Facing::ALL as $facing){
			$this->out->writeBool($name . "_" . Facing::toString($facing), isset($faces[$facing]));
		}
	}

	public function horizontalFacingFlags(array &$faces) : void{
		$name = $this->nextName();
		foreach(Facing::HORIZONTAL as $facing){
			$this->out->writeBool($name . "_" . Facing::toString($facing), isset($faces[$facing]));
		}
	}

	public function facing(int &$facing) : void{
		$this->out->writeString($this->nextName(), Facing::toString($facing));
	}

	public function facingExcept(int &$facing, int $except) : void{
		$this->out->writeString($this->nextName(), Facing::toString($facing));
	}

	public function axis(int &$axis) : void{
		$this->out->writeString($this->nextName(), Axis::toString($axis));
	}

	public function horizontalAxis(int &$axis) : void{
		$this->out->writeString($this->nextName(), Axis::toString($axis));
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
		$this->out->writeString($this->nextName(), StateValues::enumName($case));
	}

	public function enumSet(array &$set, array $allCases) : void{
		$name = $this->nextName();
		foreach($allCases as $case){
			$this->out->writeBool($name . "_" . StateValues::enumName($case), isset($set[spl_object_id($case)]));
		}
	}
}
