<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\state;

use InvalidArgumentException;
use function count;

trait StateNamesTrait{

	/** @var list<string> */
	private array $names;

	private int $next_name = 0;

	private function nextName() : string{
		$index = $this->next_name++;
		return $this->names[$index] ?? throw new InvalidArgumentException("Block describes more states than the " . count($this->names) . " state name(s) given");
	}
}
