<?php

declare(strict_types=1);

namespace kostamax27\kiln\network;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;

final class CustomItemTypeMapper{

	/** @var array<string, Closure(int) : ItemTypeEntry> */
	private array $entry_factories = [];

	/** @var array<string, Closure(int, int) : ItemTypeEntry> */
	private array $override_factories = [];

	private ?NetworkBridge $bridge = null;

	/**
	 * Injects every stored entry into the converters of the bridge, and into converters it creates later.
	 * Entries registered afterwards are injected immediately.
	 */
	public function attach(NetworkBridge $bridge) : void{
		$this->bridge === null || throw new BadMethodCallException("Item type mapper is already attached to a network bridge");
		$this->bridge = $bridge;
		foreach($bridge->getTypeConverters() as $protocol_id => $converter){
			$this->inject($protocol_id, $converter);
		}
		$bridge->addTypeConverterCreationListener(function(int $protocol_id, TypeConverter $converter) : void{
			$this->inject($protocol_id, $converter);
		});
	}

	private function inject(int $protocol_id, TypeConverter $converter) : void{
		foreach($this->entry_factories as $entry_factory){
			ItemTypeDictionaryInjector::inject($converter->getItemTypeDictionary(), $entry_factory($protocol_id));
		}
		foreach($this->override_factories as $identifier => $override_factory){
			self::applyOverride($protocol_id, $converter, $identifier, $override_factory);
		}
	}

	/**
	 * @param Closure(int) : ItemTypeEntry $entry_factory
	 */
	public function register(string $identifier, Closure $entry_factory) : void{
		!isset($this->entry_factories[$identifier]) || throw new InvalidArgumentException("Item type \"{$identifier}\" is already mapped");
		foreach($this->bridge?->getTypeConverters() ?? [] as $protocol_id => $converter){
			ItemTypeDictionaryInjector::inject($converter->getItemTypeDictionary(), $entry_factory($protocol_id));
		}
		$this->entry_factories[$identifier] = $entry_factory;
	}

	/**
	 * @param Closure(int, int) : ItemTypeEntry $entry_factory
	 */
	public function override(string $identifier, Closure $entry_factory) : void{
		!isset($this->override_factories[$identifier]) || throw new InvalidArgumentException("Item type \"{$identifier}\" is already overridden");
		foreach($this->bridge?->getTypeConverters() ?? [] as $protocol_id => $converter){
			self::applyOverride($protocol_id, $converter, $identifier, $entry_factory);
		}
		$this->override_factories[$identifier] = $entry_factory;
	}

	/**
	 * @param Closure(int, int) : ItemTypeEntry $entry_factory
	 */
	private static function applyOverride(int $protocol_id, TypeConverter $converter, string $identifier, Closure $entry_factory) : void{
		$dictionary = $converter->getItemTypeDictionary();
		$network_id = self::lookupNetworkId($converter, $identifier);
		if($network_id !== null){
			ItemTypeDictionaryInjector::replace($dictionary, $entry_factory($protocol_id, $network_id));
		}
	}

	private static function lookupNetworkId(TypeConverter $converter, string $identifier) : ?int{
		try{
			return $converter->getItemTypeDictionary()->fromStringId($identifier);
		}catch(InvalidArgumentException){
			return null;
		}
	}

	/**
	 * @return list<ItemTypeEntry>
	 */
	public function createComponentEntries(int $protocol_id) : array{
		$entries = [];
		foreach($this->entry_factories as $entry_factory){
			$entry = $entry_factory($protocol_id);
			if($entry->isComponentBased()){
				$entries[] = $entry;
			}
		}
		$converter = $this->bridge?->getTypeConverters()[$protocol_id] ?? null;
		if($converter !== null){
			foreach($this->override_factories as $identifier => $override_factory){
				$network_id = self::lookupNetworkId($converter, $identifier);
				if($network_id !== null){
					$entries[] = $override_factory($protocol_id, $network_id);
				}
			}
		}
		return $entries;
	}
}