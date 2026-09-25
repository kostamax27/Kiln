<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\permutation;

use InvalidArgumentException;
use kostamax27\kiln\block\component\BlockComponent;
use kostamax27\kiln\block\state\StateCondition;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;
use function array_map;
use function array_values;
use function count;
use function implode;

final class Permutation{

	/**
	 * Applies components while every condition holds.
	 *
	 * @param list<StateCondition> $conditions at least one.
	 */
	public static function when(array $conditions, BlockComponent ...$components) : self{
		count($conditions) > 0 || throw new InvalidArgumentException("Expected at least one state condition");
		return new self(implode(" && ", array_map(static fn(StateCondition $condition) : string => $condition->toMolang(), $conditions)), $conditions, array_values($components));
	}

	/**
	 * Applies components while an arbitrary Molang condition holds.
	 *
	 * @param string $condition e.g. "query.block_state('myplugin:stage') >= 2".
	 */
	public static function molang(string $condition, BlockComponent ...$components) : self{
		return new self(TypeValidator::validateText("Permutation condition", $condition), [], array_values($components));
	}

	/** @var array<string, BlockComponent> */
	readonly public array $components;

	/**
	 * @param list<StateCondition> $conditions typed conditions, empty for raw Molang.
	 * @param list<BlockComponent> $components
	 */
	private function __construct(
		readonly public string $condition,
		readonly public array $conditions,
		array $components
	){
		count($components) > 0 || throw new InvalidArgumentException("Permutation \"{$condition}\" must contain at least one component");
		$keyed = [];
		foreach($components as $component){
			$keyed[$component->getName()] = $component;
		}
		$this->components = $keyed;
	}

	public function toNbt(int $protocol_id) : CompoundTag{
		$components = CompoundTag::create();
		foreach($this->components as $component){
			$component->write($components, $protocol_id);
		}
		return CompoundTag::create()
			->setTag("components", $components)
			->setString("condition", $this->condition);
	}
}