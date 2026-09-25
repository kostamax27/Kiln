<?php

declare(strict_types=1);

namespace kostamax27\kiln\network;

use InvalidArgumentException;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use ReflectionProperty;
use RuntimeException;
use function get_debug_type;
use function is_array;

final class BlockItemIdMapInjector{

	private function __construct(){
	}

	public static function inject(BlockItemIdMap $map, string $identifier) : void{
		$map->lookupItemId($identifier) === null || throw new InvalidArgumentException("Block \"{$identifier}\" is already mapped to an item");
		$map->lookupBlockId($identifier) === null || throw new InvalidArgumentException("Item \"{$identifier}\" is already mapped to a block");
		foreach(["blockToItemId", "itemToBlockId"] as $name){
			$property = new ReflectionProperty(BlockItemIdMap::class, $name);
			$value = $property->getValue($map);
			is_array($value) || throw new RuntimeException("Expected BlockItemIdMap::\${$name} to be an array, got " . get_debug_type($value));
			$value[$identifier] = $identifier;
			$property->setValue($map, $value);
		}
	}
}