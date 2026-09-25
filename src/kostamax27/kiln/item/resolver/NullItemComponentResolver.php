<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\resolver;

use pocketmine\item\Item;

final class NullItemComponentResolver implements ItemComponentResolver{

	private static self $instance;

	/**
	 * Returns the resolver that derives nothing, leaving every component to the builder.
	 */
	public static function instance() : self{
		return self::$instance ??= new self();
	}

	private function __construct(){
	}

	public function resolveComponents(string $identifier, Item $item) : array{
		return [];
	}

	public function resolveProperties(string $identifier, Item $item) : array{
		return [];
	}
}
