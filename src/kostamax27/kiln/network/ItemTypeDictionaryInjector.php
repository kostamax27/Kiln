<?php

declare(strict_types=1);

namespace kostamax27\kiln\network;

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use function get_debug_type;
use function is_array;

final class ItemTypeDictionaryInjector{

	private static ReflectionProperty $item_types;
	private static ReflectionProperty $string_to_int;
	private static ReflectionProperty $int_to_string;

	public static function replace(ItemTypeDictionary $dictionary, ItemTypeEntry $entry) : void{
		$string_id = $entry->getStringId();
		$dictionary->fromStringId($string_id) === $entry->getNumericId() || throw new InvalidArgumentException("Item type dictionary maps \"{$string_id}\" to a different numeric ID than {$entry->getNumericId()}");
		$property = new ReflectionProperty(ItemTypeDictionary::class, "itemTypes");
		$item_types = $property->getValue($dictionary);
		is_array($item_types) || throw new RuntimeException("Expected ItemTypeDictionary::\$itemTypes to be an array, got " . get_debug_type($item_types));
		foreach($item_types as $index => $existing){
			if($existing instanceof ItemTypeEntry && $existing->getStringId() === $string_id){
				$item_types[$index] = $entry;
				$property->setValue($dictionary, $item_types);
				return;
			}
		}
		throw new InvalidArgumentException("Item type dictionary has no entry for \"{$string_id}\"");
	}

	public static function inject(ItemTypeDictionary $dictionary, ItemTypeEntry $entry) : void{
		if(!isset(self::$item_types)){
			$reflection = new ReflectionClass(ItemTypeDictionary::class);
			self::$item_types = $reflection->getProperty("itemTypes");
			self::$string_to_int = $reflection->getProperty("stringToIntMap");
			self::$int_to_string = $reflection->getProperty("intToStringIdMap");
		}

		$item_types = self::$item_types->getValue($dictionary);
		$string_to_int = self::$string_to_int->getValue($dictionary);
		$int_to_string = self::$int_to_string->getValue($dictionary);
		is_array($item_types) || throw new RuntimeException("Expected ItemTypeDictionary::\$itemTypes to be an array, got " . get_debug_type($item_types));
		is_array($string_to_int) || throw new RuntimeException("Expected ItemTypeDictionary::\$stringToIntMap to be an array, got " . get_debug_type($string_to_int));
		is_array($int_to_string) || throw new RuntimeException("Expected ItemTypeDictionary::\$intToStringIdMap to be an array, got " . get_debug_type($int_to_string));

		$string_id = $entry->getStringId();
		$numeric_id = $entry->getNumericId();
		!isset($string_to_int[$string_id]) || throw new InvalidArgumentException("Item type dictionary already contains string ID \"{$string_id}\"");
		!isset($int_to_string[$numeric_id]) || throw new InvalidArgumentException("Item type dictionary already contains numeric ID {$numeric_id}");

		$item_types[] = $entry;
		$string_to_int[$string_id] = $numeric_id;
		$int_to_string[$numeric_id] = $string_id;
		self::$item_types->setValue($dictionary, $item_types);
		self::$string_to_int->setValue($dictionary, $string_to_int);
		self::$int_to_string->setValue($dictionary, $int_to_string);
	}
}