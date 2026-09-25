<?php

declare(strict_types=1);

namespace kostamax27\kiln\util;

use function array_map;
use function implode;

final class Molang{

	private function __construct(){
	}

	/**
	 * @param list<string> $tags
	 */
	public static function anyTag(string $key, array $tags) : string{
		TypeValidator::validateNonEmptyList($key, $tags);
		return "query.any_tag(" . implode(", ", array_map(static fn(string $tag) : string => "'" . TypeValidator::validateTag("{$key} tag", $tag) . "'", $tags)) . ")";
	}
}