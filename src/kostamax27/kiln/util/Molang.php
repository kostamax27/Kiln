<?php

declare(strict_types=1);

namespace kostamax27\kiln\util;

use kostamax27\kiln\network\ProtocolVersions;
use RuntimeException;
use function array_map;
use function implode;
use function preg_replace;

final class Molang{

	private const BLOCK_STATE_QUERY_PATTERN = "/\\b(query|q)\\.(has_)?block_state\\b/i";

	private function __construct(){
	}

	/**
	 * @param list<string> $tags
	 */
	public static function anyTag(string $key, array $tags) : string{
		TypeValidator::validateNonEmptyList($key, $tags);
		return "query.any_tag(" . implode(", ", array_map(static fn(string $tag) : string => "'" . TypeValidator::validateTag("{$key} tag", $tag) . "'", $tags)) . ")";
	}

	/**
	 * Adapts an expression to the Molang understood by clients of the given protocol. Clients before 1.20.10
	 * only know query.block_property() and query.has_block_property(), which were renamed to
	 * query.block_state() and query.has_block_state() afterwards.
	 *
	 * @param string $expression expression written against the current Molang, e.g. "query.block_state('myplugin:lit')".
	 */
	public static function forProtocol(string $expression, int $protocol_id) : string{
		if($protocol_id >= ProtocolVersions::V1_20_10){
			return $expression;
		}
		return preg_replace(self::BLOCK_STATE_QUERY_PATTERN, "\$1.\$2block_property", $expression) ?? throw new RuntimeException("Failed to rewrite Molang expression \"{$expression}\"");
	}
}
