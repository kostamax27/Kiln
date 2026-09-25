<?php

declare(strict_types=1);

namespace kostamax27\kiln\item;

use InvalidArgumentException;
use kostamax27\kiln\creative\CreativeGroupResolver;
use kostamax27\kiln\network\CustomItemTypeMapper;
use pocketmine\data\bedrock\item\ItemTypeSerializeException;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\world\format\io\GlobalItemDataHandlers;

final class CustomItemRegistry{

	public const MAX_NETWORK_ID = 32767;

	/** @var array<string, CustomItem> */
	private array $items = [];

	/** @var array<int, CustomItem> */
	private array $items_by_type_id = [];

	/** @var array<string, CustomItemVariant> */
	private array $variants = [];

	/** @var array<string, CustomItem> */
	private array $overrides = [];

	public function __construct(
		readonly private CustomItemTypeMapper $type_mapper,
		readonly private CreativeGroupResolver $creative_groups
	){}

	/**
	 * Registers a custom item so that every variant of it can be saved, parsed by commands such as
	 * /give and rendered by clients.
	 */
	public function register(CustomItem $item) : void{
		$type_id = $item->item->getTypeId();
		!isset($this->items[$item->identifier]) || throw new InvalidArgumentException("Custom item \"{$item->identifier}\" is already registered");
		!isset($this->items_by_type_id[$type_id]) || throw new InvalidArgumentException("Item type ID {$type_id} is already used by custom item \"{$this->items_by_type_id[$type_id]->identifier}\"");
		$deserializer = GlobalItemDataHandlers::getDeserializer();
		$parser = StringToItemParser::getInstance();
		$identifiers = [];
		foreach($item->variants as $state_id => $variant){
			$identifier = $variant->identifier;
			!isset($this->variants[$identifier]) || throw new InvalidArgumentException("Custom item \"{$identifier}\" is already registered");
			$variant->network_id > 0 && $variant->network_id <= self::MAX_NETWORK_ID || throw new InvalidArgumentException("Network ID of \"{$identifier}\" must be in range [1, " . self::MAX_NETWORK_ID . "], got {$variant->network_id}");
			$deserializer->getDeserializerForId($identifier) === null || throw new InvalidArgumentException("Item ID \"{$identifier}\" already has a deserializer registered");
			$parser->parse($identifier) === null || throw new InvalidArgumentException("Alias \"{$identifier}\" is already registered in " . StringToItemParser::class);
			$identifiers[$state_id] = $identifier;
		}

		GlobalItemDataHandlers::getSerializer()->map(clone $item->item, static fn(Item $state) : SavedItemData => new SavedItemData($identifiers[$state->getStateId()] ?? throw new ItemTypeSerializeException("Item {$state->getName()} is in a state without a variant")));
		foreach($item->variants as $variant){
			$identifier = $variant->identifier;
			$prototype = clone $variant->item;
			$deserializer->map($identifier, static fn() : Item => clone $prototype);
			$parser->register($identifier, static fn() : Item => clone $prototype);
			$network_id = $variant->network_id;
			$this->type_mapper->register($identifier, static fn(int $protocol_id) : ItemTypeEntry => $variant->createTypeEntry($protocol_id, $network_id));
			$this->variants[$identifier] = $variant;

			$category = $variant->creative_info->toCreativeCategory();
			if($category !== null){
				$group = $variant->creative_info->group === null ? null : $this->creative_groups->resolve($category, $variant->creative_info->group, $prototype);
				CreativeInventory::getInstance()->add(clone $prototype, $category, $group);
			}
		}
		$this->items[$item->identifier] = $item;
		$this->items_by_type_id[$type_id] = $item;
	}

	/**
	 * Replaces the client-side definition of an existing item, such as a vanilla item built with
	 * {@see CustomItemBuilder::override()}.
	 */
	public function override(CustomItem $item) : void{
		foreach($item->variants as $variant){
			$identifier = $variant->identifier;
			!isset($this->variants[$identifier]) || throw new InvalidArgumentException("\"{$identifier}\" is a custom item, change its definition before registering it instead");
			!isset($this->overrides[$identifier]) || throw new InvalidArgumentException("Item \"{$identifier}\" is already overridden");
			GlobalItemDataHandlers::getDeserializer()->getDeserializerForId($identifier) !== null || throw new InvalidArgumentException("Item \"{$identifier}\" does not exist, register custom items with register()");
			$this->type_mapper->override($identifier, static fn(int $protocol_id, int $network_id) : ItemTypeEntry => $variant->createTypeEntry($protocol_id, $network_id));
			$this->overrides[$identifier] = $item;
		}
	}

	/**
	 * Returns the override applied to the item with the given identifier, or null if there is none.
	 */
	public function getOverrideNullable(string $identifier) : ?CustomItem{
		return $this->overrides[$identifier] ?? null;
	}

	/**
	 * Returns every applied override keyed by item identifier.
	 *
	 * @return array<string, CustomItem>
	 */
	public function getOverrides() : array{
		return $this->overrides;
	}

	/**
	 * Returns the definition registered with the given identifier; use {@see self::createItem()} for an item.
	 */
	public function get(string $identifier) : CustomItem{
		return $this->items[$identifier] ?? throw new InvalidArgumentException("Custom item \"{$identifier}\" is not registered");
	}

	/**
	 * Returns the custom item registered with the given identifier, or null if there is none.
	 */
	public function getNullable(string $identifier) : ?CustomItem{
		return $this->items[$identifier] ?? null;
	}

	/**
	 * Returns the custom item definition the given item instance belongs to, if any.
	 */
	public function getFromItem(Item $item) : ?CustomItem{
		return $this->items_by_type_id[$item->getTypeId()] ?? null;
	}

	/**
	 * Returns the variant registered with the given identifier, e.g. "myplugin:gem_ruby", or null if
	 * there is none. Items without state have one variant with the item's own identifier.
	 */
	public function getVariantNullable(string $identifier) : ?CustomItemVariant{
		return $this->variants[$identifier] ?? null;
	}

	/**
	 * Returns a new item: in the state of the variant with the given identifier (e.g. "myplugin:gem_ruby"),
	 * or in its default state when given the identifier it was registered with (e.g. "myplugin:gem").
	 */
	public function createItem(string $identifier, int $count = 1) : Item{
		$item = $this->variants[$identifier]->item ?? $this->items[$identifier]->item ?? throw new InvalidArgumentException("Custom item \"{$identifier}\" is not registered");
		return (clone $item)->setCount($count);
	}

	/**
	 * Returns every registered custom item keyed by identifier.
	 *
	 * @return array<string, CustomItem>
	 */
	public function getAll() : array{
		return $this->items;
	}
}
