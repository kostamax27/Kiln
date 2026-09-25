<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\state;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use UnitEnum;

final class StateCondition{

	/**
	 * Matches an integer state, e.g. one described with boundedIntAuto().
	 *
	 * @param string $name state name given to {@see \kostamax27\kiln\block\CustomBlockBuilder::setStateNames()}.
	 */
	public static function int(string $name, int $value) : self{
		return new self($name, new IntTag($value));
	}

	/**
	 * Matches a boolean state, including a single face of facingFlags() ("myplugin:faces_up").
	 */
	public static function bool(string $name, bool $value) : self{
		return new self($name, new ByteTag($value ? 1 : 0));
	}

	/**
	 * Matches a state described with facing(), horizontalFacing() or facingExcept().
	 *
	 * @param int $facing Facing constant.
	 */
	public static function facing(string $name, int $facing) : self{
		return new self($name, new StringTag(Facing::toString($facing)));
	}

	/**
	 * Matches a state described with axis() or horizontalAxis().
	 *
	 * @param int $axis Axis constant.
	 */
	public static function axis(string $name, int $axis) : self{
		return new self($name, new StringTag(Axis::toString($axis)));
	}

	/**
	 * Matches a state described with enum().
	 */
	public static function enum(string $name, UnitEnum $case) : self{
		return new self($name, new StringTag(StateValues::enumName($case)));
	}

	private function __construct(
		readonly public string $name,
		readonly public Tag $value
	){
		TypeValidator::validateTag("State name", $name);
	}

	/**
	 * Returns the condition as a Molang expression, e.g. query.block_state('myplugin:stage') == 3.
	 */
	public function toMolang() : string{
		return "query.block_state('{$this->name}') == " . BlockProperty::formatMolang($this->value);
	}
}