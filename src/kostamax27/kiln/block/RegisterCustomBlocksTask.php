<?php

declare(strict_types=1);

namespace kostamax27\kiln\block;

use Closure;
use kostamax27\kiln\network\CustomBlockStateMapper;
use kostamax27\kiln\network\NetworkBridge;
use pmmp\thread\ThreadSafeArray;
use pocketmine\block\Block;
use pocketmine\scheduler\AsyncTask;
use RuntimeException;
use function get_debug_type;
use function is_string;

final class RegisterCustomBlocksTask extends AsyncTask{

	/** @var ThreadSafeArray<int, ThreadSafeArray<int, mixed>> */
	private ThreadSafeArray $entries;

	/**
	 * @param list<array{string, Closure() : Block, list<string>}> $entries
	 * @param class-string<NetworkBridge> $bridge_class bridge the main thread uses; created again on the worker.
	 */
	public function __construct(array $entries, private string $bridge_class){
		$this->entries = new ThreadSafeArray();
		foreach($entries as [$identifier, $factory, $state_names]){
			$names = new ThreadSafeArray();
			foreach($state_names as $name){
				$names[] = $name;
			}
			$entry = new ThreadSafeArray();
			$entry[] = $identifier;
			$entry[] = $factory;
			$entry[] = $names;
			$this->entries[] = $entry;
		}
	}

	public function onRun() : void{
		$states = [];
		foreach($this->entries as $entry){
			$entry instanceof ThreadSafeArray || throw new RuntimeException("Expected a block entry, got " . get_debug_type($entry));
			[$identifier, $factory, $names] = [$entry[0], $entry[1], $entry[2]];
			is_string($identifier) || throw new RuntimeException("Expected block identifier to be a string, got " . get_debug_type($identifier));
			$factory instanceof Closure || throw new RuntimeException("Expected factory of block \"{$identifier}\" to be a closure, got " . get_debug_type($factory));
			$names instanceof ThreadSafeArray || throw new RuntimeException("Expected state names of block \"{$identifier}\", got " . get_debug_type($names));
			$state_names = [];
			foreach($names as $name){
				is_string($name) || throw new RuntimeException("Expected state name of block \"{$identifier}\" to be a string, got " . get_debug_type($name));
				$state_names[] = $name;
			}

			$block = $factory();
			$block instanceof Block || throw new RuntimeException("Factory of block \"{$identifier}\" returned " . get_debug_type($block));
			$states[$identifier] = BlockRuntimeRegistrar::register($identifier, $block, $state_names, false);
		}
		(new CustomBlockStateMapper(new $this->bridge_class()))->register($states);
	}
}
