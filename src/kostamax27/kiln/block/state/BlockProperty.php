<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\state;

use InvalidArgumentException;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use function count;
use function get_debug_type;
use function str_contains;
use function str_starts_with;

final class BlockProperty{

	public const MAX_VALUES = 16;

	/**
	 * Properties are derived from the block's describeBlockItemState() and describeBlockOnlyState() by
	 * {@see StateSchemaDescriber}.
	 *
	 * @param string $name namespaced property name.
	 * @param non-empty-list<Tag> $values values of one tag type, in the order clients number them.
	 */
	public function __construct(
		readonly public string $name,
		readonly public array $values
	){
		self::validateName($name);
		TypeValidator::validateInt("Value count of property \"{$name}\"", count($values), 1, self::MAX_VALUES);
		foreach($values as $index => $value){
			$value::class === $values[0]::class || throw new InvalidArgumentException("Values of property \"{$name}\" must share one tag type, got " . $values[0]::class . " and " . $value::class);
			for($other = 0; $other < $index; ++$other){
				!$values[$other]->equals($value) || throw new InvalidArgumentException("Values of property \"{$name}\" must be distinct, {$value} is repeated");
			}
		}
	}

	/**
	 * Validates a namespaced, non-vanilla property name such as "myplugin:stage".
	 */
	public static function validateName(string $name) : void{
		TypeValidator::validateTag("Property name", $name);
		str_contains($name, ":") || throw new InvalidArgumentException("Property name must be namespaced, e.g. \"myplugin:stage\", got \"{$name}\"");
		!str_starts_with($name, "minecraft:") || throw new InvalidArgumentException("Property name cannot use the reserved \"minecraft\" namespace, got \"{$name}\"");
	}

	/**
	 * Returns whether the tag is one of the values of this property.
	 */
	public function accepts(Tag $value) : bool{
		foreach($this->values as $candidate){
			if($candidate->equals($value)){
				return true;
			}
		}
		return false;
	}

	/**
	 * Formats a value as a Molang literal comparable with query.block_state().
	 */
	public static function formatMolang(Tag $value) : string{
		return match(true){
			$value instanceof ByteTag => $value->getValue() !== 0 ? "true" : "false",
			$value instanceof IntTag => (string) $value->getValue(),
			$value instanceof StringTag => "'{$value->getValue()}'",
			default => throw new InvalidArgumentException("Block states are bytes, ints or strings, got " . get_debug_type($value))
		};
	}

	public function toNbt() : CompoundTag{
		return CompoundTag::create()
			->setTag("enum", new ListTag($this->values, $this->values[0]->getType()))
			->setString("name", $this->name);
	}

	/**
	 * Generates every combination of property values in the order clients number block states: the first
	 * property varies fastest.
	 *
	 * @param list<self> $properties
	 * @return non-empty-list<array<string, Tag>>
	 */
	public static function generateStates(array $properties) : array{
		$states = [[]];
		foreach($properties as $property){
			$next = [];
			foreach($property->values as $value){
				foreach($states as $state){
					$state[$property->name] = $value;
					$next[] = $state;
				}
			}
			$states = $next;
		}
		return $states;
	}
}
