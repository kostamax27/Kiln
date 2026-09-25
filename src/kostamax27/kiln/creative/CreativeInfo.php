<?php

declare(strict_types=1);

namespace kostamax27\kiln\creative;

use InvalidArgumentException;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\inventory\CreativeCategory;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use function is_string;
use function str_starts_with;

final class CreativeInfo{

	public const CATEGORY_NONE = 0; // hidden from the creative inventory
	public const CATEGORY_CONSTRUCTION = 1;
	public const CATEGORY_NATURE = 2;
	public const CATEGORY_EQUIPMENT = 3;
	public const CATEGORY_ITEMS = 4;

	public const GROUP_ARROW = "itemGroup.name.arrow";
	public const GROUP_AXE = "itemGroup.name.axe";
	public const GROUP_BOOTS = "itemGroup.name.boots";
	public const GROUP_CHESTPLATE = "itemGroup.name.chestplate";
	public const GROUP_COOKED_FOOD = "itemGroup.name.cookedFood";
	public const GROUP_CROP = "itemGroup.name.crop";
	public const GROUP_DYE = "itemGroup.name.dye";
	public const GROUP_HELMET = "itemGroup.name.helmet";
	public const GROUP_HOE = "itemGroup.name.hoe";
	public const GROUP_LEGGINGS = "itemGroup.name.leggings";
	public const GROUP_MISC_FOOD = "itemGroup.name.miscFood";
	public const GROUP_ORE = "itemGroup.name.ore";
	public const GROUP_PICKAXE = "itemGroup.name.pickaxe";
	public const GROUP_POTION = "itemGroup.name.potion";
	public const GROUP_RAW_FOOD = "itemGroup.name.rawFood";
	public const GROUP_RECORD = "itemGroup.name.record";
	public const GROUP_SEED = "itemGroup.name.seed";
	public const GROUP_SHOVEL = "itemGroup.name.shovel";
	public const GROUP_SPEAR = "itemGroup.name.spear";
	public const GROUP_SWORD = "itemGroup.name.sword";

	/**
	 * Registers the item without listing it in the creative inventory.
	 *
	 * @return self / Returns the category and group the item currently has in the creative inventory, or {@see self::none()} if it is not listed.
	 */
	public static function fromInventory(CreativeInventory $inventory, Item $item) : self{
		$entry = $inventory->getEntry($inventory->getItemIndex($item));
		if($entry === null){
			return self::none();
		}
		$category = match($entry->getCategory()){
			CreativeCategory::CONSTRUCTION => self::CATEGORY_CONSTRUCTION,
			CreativeCategory::NATURE => self::CATEGORY_NATURE,
			CreativeCategory::EQUIPMENT => self::CATEGORY_EQUIPMENT,
			CreativeCategory::ITEMS => self::CATEGORY_ITEMS
		};
		$group = $entry->getGroup()?->getName();
		return new self($category, $group === null ? null : (is_string($group) ? $group : $group->getText()));
	}

	/**
	 * Registers the item without listing it in the creative inventory.
	 */
	public static function none() : self{
		return new self(self::CATEGORY_NONE);
	}

	/**
	 * Lists the item in the "Items" tab.
	 *
	 * @param string|null $group vanilla group translation key (self::GROUP_*) or a custom group name.
	 */
	public static function items(?string $group = null) : self{
		return new self(self::CATEGORY_ITEMS, $group);
	}

	/**
	 * Lists the item in the "Equipment" tab.
	 *
	 * @param string|null $group vanilla group translation key (self::GROUP_*) or a custom group name.
	 */
	public static function equipment(?string $group = null) : self{
		return new self(self::CATEGORY_EQUIPMENT, $group);
	}

	/**
	 * Lists the item in the "Nature" tab.
	 *
	 * @param string|null $group vanilla group translation key (self::GROUP_*) or a custom group name.
	 */
	public static function nature(?string $group = null) : self{
		return new self(self::CATEGORY_NATURE, $group);
	}

	/**
	 * Lists the item in the "Construction" tab.
	 *
	 * @param string|null $group vanilla group translation key (self::GROUP_*) or a custom group name.
	 */
	public static function construction(?string $group = null) : self{
		return new self(self::CATEGORY_CONSTRUCTION, $group);
	}

	/**
	 * @param int $category one of self::CATEGORY_*.
	 * @param string|null $group vanilla group translation key (self::GROUP_*) or a custom group name.
	 */
	public function __construct(
		readonly public int $category,
		readonly public ?string $group = null
	){
		$category >= self::CATEGORY_NONE && $category <= self::CATEGORY_ITEMS || throw new InvalidArgumentException("Invalid creative category {$category}");
		if($group !== null){
			TypeValidator::validateText("Creative group", $group);
		}
		$group === null || $category !== self::CATEGORY_NONE || throw new InvalidArgumentException("Items hidden from the creative inventory cannot belong to a group");
	}

	/**
	 * Returns the category name used in block definitions ("menu_category").
	 */
	public function toMenuCategory() : string{
		return match($this->category){
			self::CATEGORY_CONSTRUCTION => "construction",
			self::CATEGORY_NATURE => "nature",
			self::CATEGORY_EQUIPMENT => "equipment",
			self::CATEGORY_ITEMS => "items",
			default => "none"
		};
	}

	/**
	 * Returns the group name used in block definitions ("menu_category"), where vanilla groups carry the
	 * "minecraft:" namespace.
	 */
	public function toMenuGroup() : string{
		return match(true){
			$this->group === null => "",
			str_starts_with($this->group, "itemGroup.") => "minecraft:{$this->group}",
			default => $this->group
		};
	}

	/**
	 * Returns the PocketMine creative category, or null if the item is hidden.
	 */
	public function toCreativeCategory() : ?CreativeCategory{
		return match($this->category){
			self::CATEGORY_CONSTRUCTION => CreativeCategory::CONSTRUCTION,
			self::CATEGORY_NATURE => CreativeCategory::NATURE,
			self::CATEGORY_EQUIPMENT => CreativeCategory::EQUIPMENT,
			self::CATEGORY_ITEMS => CreativeCategory::ITEMS,
			default => null
		};
	}
}