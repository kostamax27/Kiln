<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\resolver;

use pocketmine\block\Block;

final class NullBlockComponentResolver implements BlockComponentResolver{

	private static self $instance;

	/**
	 * Returns the resolver that derives nothing, leaving every component to the builder.
	 */
	public static function instance() : self{
		return self::$instance ??= new self();
	}

	private function __construct(){
	}

	public function resolve(string $identifier, Block $block) : array{
		return [];
	}
}