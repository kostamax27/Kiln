<?php

declare(strict_types=1);

namespace kostamax27\kiln\network;

use Closure;
use pocketmine\nbt\tag\Tag;
use pocketmine\network\mcpe\convert\BlockStateDictionaryEntry;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkSession;

interface NetworkBridge{

	/**
	 * Returns every {@see TypeConverter} that currently exists, keyed by the protocol ID it translates
	 * for.
	 *
	 * @return array<int, TypeConverter>
	 */
	public function getTypeConverters() : array;

	/**
	 * Registers a listener that is called whenever a new {@see TypeConverter} is created after this call.
	 *
	 * @param Closure(int, TypeConverter) : void $listener
	 */
	public function addTypeConverterCreationListener(Closure $listener) : void;

	/**
	 * Returns the protocol ID the given session communicates with.
	 */
	public function getProtocolId(NetworkSession $session) : int;

	/**
	 * Returns the lowest protocol ID this server accepts.
	 */
	public function getMinimumProtocolId() : int;

	/**
	 * Creates a block state dictionary entry.
	 *
	 * @param string $name block identifier.
	 * @param array<string, Tag> $states
	 * @param int $meta legacy meta value, used when mapping crafting recipe inputs.
	 */
	public function createBlockStateDictionaryEntry(string $name, array $states, int $meta) : BlockStateDictionaryEntry;
}
