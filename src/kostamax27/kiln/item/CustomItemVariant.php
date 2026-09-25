<?php

declare(strict_types=1);

namespace kostamax27\kiln\item;

use kostamax27\kiln\creative\CreativeInfo;
use kostamax27\kiln\item\component\ItemComponent;
use kostamax27\kiln\item\property\ItemProperty;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;

final class CustomItemVariant{

	public const ITEM_TYPE_VERSION_DATA_DRIVEN = 1;

	/**
	 * One client-side item. Items with state have one variant per state.
	 *
	 * @param string $identifier namespaced identifier clients know the variant by.
	 * @param Item $item server-side item in the state this variant represents.
	 * @param int $network_id numeric ID of custom items; overrides use the ID of the item they replace.
	 * @param array<string, ItemComponent> $components keyed by {@see ItemComponent::getName()}.
	 * @param array<string, ItemProperty> $properties keyed by {@see ItemProperty::getName()}.
	 */
	public function __construct(
		readonly public string $identifier,
		readonly public Item $item,
		readonly public int $network_id,
		readonly public array $components,
		readonly public array $properties,
		readonly public CreativeInfo $creative_info
	){}

	/**
	 * Encodes the item definition sent to clients of the given protocol.
	 *
	 * @param int $network_id numeric ID of the item in that protocol.
	 */
	public function encode(int $protocol_id, int $network_id) : CompoundTag{
		$components = CompoundTag::create();
		$properties = self::createDefaultProperties();
		foreach($this->properties as $property){
			$property->write($properties, $protocol_id);
		}
		foreach($this->components as $component){
			$component->write($components, $properties, $protocol_id);
		}
		$properties->setInt("creative_category", $this->creative_info->category);
		$properties->setString("creative_group", $this->creative_info->group ?? "");
		$components->setTag("item_properties", $properties);
		return CompoundTag::create()
			->setTag("components", $components)
			->setInt("id", $network_id)
			->setString("name", $this->identifier);
	}

	/**
	 * Creates the item registry entry sent to clients of the given protocol.
	 *
	 * @param int $network_id numeric ID of the item in that protocol.
	 */
	public function createTypeEntry(int $protocol_id, int $network_id) : ItemTypeEntry{
		return new ItemTypeEntry($this->identifier, $network_id, true, self::ITEM_TYPE_VERSION_DATA_DRIVEN, new CacheableNbt($this->encode($protocol_id, $network_id)));
	}

	private static function createDefaultProperties() : CompoundTag{
		return CompoundTag::create()
			->setByte("allow_off_hand", 0)
			->setByte("can_destroy_in_creative", 1)
			->setInt("damage", 0)
			->setString("enchantable_slot", "none")
			->setInt("enchantable_value", 0)
			->setByte("foil", 0)
			->setInt("frame_count", 1)
			->setByte("hand_equipped", 0)
			->setByte("liquid_clipped", 0)
			->setInt("max_stack_size", 64)
			->setFloat("mining_speed", 1.0)
			->setByte("should_despawn", 1)
			->setByte("stacked_by_data", 0)
			->setInt("use_animation", 0)
			->setInt("use_duration", 0);
	}
}