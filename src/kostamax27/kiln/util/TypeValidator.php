<?php

declare(strict_types=1);

namespace kostamax27\kiln\util;

use InvalidArgumentException;
use function count;
use function implode;
use function in_array;
use function is_finite;
use function preg_match;
use function trim;
use const INF;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

final class TypeValidator{

	public const IDENTIFIER_PATTERN = "/^(?:[a-z0-9_.\-]+:)?[a-z0-9_.\/\-]+$/";
	public const ACTOR_IDENTIFIER_PATTERN = "/^(?:\w+(?:\.\w+)*:(?=\w))?\w+(?:\.\w+)*(?:<(?:(?:\w+(?:\.\w+)*:(?=\w))?\w+(?:\.\w+)*)*>)?$/";
	public const TAG_PATTERN = "/^[a-z0-9_.:\-]+$/";
	public const TEXTURE_PATTERN = "/^[A-Za-z0-9_.:\/\-]+$/";
	public const HEX_COLOR_PATTERN = "/^#[0-9a-fA-F]{6}$/";
	public const NAME_PATTERN = "/^[a-z0-9_.]+$/";

	private function __construct(){
	}

	public static function validateIdentifier(string $key, string $value) : string{
		return self::validatePattern($key, $value, self::IDENTIFIER_PATTERN, "an identifier such as \"minecraft:stone\"");
	}

	public static function validateActorIdentifier(string $key, string $value) : string{
		return self::validatePattern($key, $value, self::ACTOR_IDENTIFIER_PATTERN, "an entity identifier such as \"minecraft:snowball\"");
	}

	public static function validateTag(string $key, string $value) : string{
		return self::validatePattern($key, $value, self::TAG_PATTERN, "a tag such as \"minecraft:is_food\"");
	}

	public static function validateTexture(string $key, string $value) : string{
		return self::validatePattern($key, $value, self::TEXTURE_PATTERN, "a texture key such as \"myplugin_ruby\"");
	}

	public static function validateHexColor(string $key, string $value) : string{
		return self::validatePattern($key, $value, self::HEX_COLOR_PATTERN, "a hex color such as \"#47ff5a\"");
	}

	/**
	 * Validates a lowercase client name such as a sound event ("record.cat") or a particle type.
	 */
	public static function validateName(string $key, string $value) : string{
		return self::validatePattern($key, $value, self::NAME_PATTERN, "a lowercase name such as \"record.cat\"");
	}

	/**
	 * @param list<string> $choices
	 */
	public static function validateChoice(string $key, string $value, array $choices) : string{
		in_array($value, $choices, true) || throw new InvalidArgumentException("{$key} must be one of \"" . implode("\", \"", $choices) . "\", got \"{$value}\"");
		return $value;
	}

	public static function validateText(string $key, string $value) : string{
		trim($value) !== "" || throw new InvalidArgumentException("{$key} must not be blank");
		return $value;
	}

	public static function validateInt(string $key, int $value, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX) : int{
		$value >= $min && $value <= $max || throw new InvalidArgumentException("{$key} must be in range [{$min}, {$max}], got {$value}");
		return $value;
	}

	public static function validateFloat(string $key, float $value, float $min = -INF, float $max = INF) : float{
		is_finite($value) || throw new InvalidArgumentException("{$key} must be a finite number, got {$value}");
		$value >= $min && $value <= $max || throw new InvalidArgumentException("{$key} must be in range [{$min}, {$max}], got {$value}");
		return $value;
	}

	/**
	 * @template T
	 * @param list<T> $values
	 * @return non-empty-list<T>
	 */
	public static function validateNonEmptyList(string $key, array $values) : array{
		count($values) > 0 || throw new InvalidArgumentException("{$key} must contain at least one entry");
		return $values;
	}

	private static function validatePattern(string $key, string $value, string $pattern, string $expectation) : string{
		preg_match($pattern, $value) === 1 || throw new InvalidArgumentException("{$key} must be {$expectation}, got \"{$value}\"");
		return $value;
	}
}
