<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\resolver;

use kostamax27\kiln\item\component\CooldownComponent;
use kostamax27\kiln\item\component\DamageComponent;
use kostamax27\kiln\item\component\DiggerComponent;
use kostamax27\kiln\item\component\DisplayNameComponent;
use kostamax27\kiln\item\component\DurabilityComponent;
use kostamax27\kiln\item\component\EnchantableComponent;
use kostamax27\kiln\item\component\FoodComponent;
use kostamax27\kiln\item\component\FuelComponent;
use kostamax27\kiln\item\component\HandEquippedComponent;
use kostamax27\kiln\item\component\MaxStackSizeComponent;
use kostamax27\kiln\item\component\TagsComponent;
use kostamax27\kiln\item\component\UseAnimationComponent;
use kostamax27\kiln\item\component\UseModifiersComponent;
use kostamax27\kiln\item\component\value\BlockDescriptor;
use kostamax27\kiln\item\component\value\DiggerSpeed;
use kostamax27\kiln\item\component\WearableComponent;
use kostamax27\kiln\item\property\CanDestroyInCreativeProperty;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\Bow;
use pocketmine\item\ConsumableItem;
use pocketmine\item\Durable;
use pocketmine\item\FishingRod;
use pocketmine\item\FlintSteel;
use pocketmine\item\FoodSourceItem;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shears;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\item\TieredTool;
use pocketmine\item\Tool;
use pocketmine\item\ToolTier;
use function count;
use function max;

final class VanillaItemComponentResolver implements ItemComponentResolver{

	public const CONSUME_DURATION = 1.6;
	public const CONSUME_MOVEMENT_MODIFIER = 0.35;

	private static self $instance;

	/**
	 * Returns the resolver that derives components from PocketMine item classes (Durable, Armor, Food,
	 * TieredTool…).
	 */
	public static function instance() : self{
		return self::$instance ??= new self();
	}

	private function __construct(){
	}

	public function resolveProperties(string $identifier, Item $item) : array{
		return [new CanDestroyInCreativeProperty(!($item instanceof Sword))];
	}

	public function resolveComponents(string $identifier, Item $item) : array{
		$components = [
			new DisplayNameComponent($item->getVanillaName()),
			new MaxStackSizeComponent($item->getMaxStackSize())
		];
		$tags = [];

		if($item instanceof Durable){
			$components[] = new DurabilityComponent($item->getMaxDurability());
		}
		if($item instanceof Tool){
			$components[] = new HandEquippedComponent(true);
		}
		if(($attack_points = $item->getAttackPoints()) > 1){
			$components[] = new DamageComponent($attack_points);
		}
		if(($fuel_time = $item->getFuelTime()) > 0){
			$components[] = new FuelComponent($fuel_time / 20);
		}
		if(($cooldown = $item->getCooldownTicks()) > 0){
			$components[] = new CooldownComponent($item->getCooldownTag() ?? $identifier, $cooldown / 20);
		}
		if(($enchantability = $item->getEnchantability()) > 0 && ($slot = self::resolveEnchantableSlot($item)) !== null){
			$components[] = new EnchantableComponent($slot, $enchantability);
		}

		if($item instanceof Armor){
			$slot = match($item->getArmorSlot()){
				ArmorInventory::SLOT_HEAD => WearableComponent::SLOT_ARMOR_HEAD,
				ArmorInventory::SLOT_CHEST => WearableComponent::SLOT_ARMOR_CHEST,
				ArmorInventory::SLOT_LEGS => WearableComponent::SLOT_ARMOR_LEGS,
				default => WearableComponent::SLOT_ARMOR_FEET
			};
			$components[] = new WearableComponent($slot, $item->getDefensePoints());
			$tags[] = "minecraft:is_armor";
		}

		if($item instanceof FoodSourceItem){
			$nutrition = $item->getFoodRestore();
			$components[] = new FoodComponent($nutrition, $item->getSaturationRestore() / (2 * max(1, $nutrition)), !$item->requiresHunger());
			$components[] = new UseAnimationComponent(UseAnimationComponent::ANIMATION_EAT);
			$components[] = new UseModifiersComponent(self::CONSUME_DURATION, self::CONSUME_MOVEMENT_MODIFIER);
			$tags[] = "minecraft:is_food";
		}elseif($item instanceof ConsumableItem){
			$components[] = new UseAnimationComponent(UseAnimationComponent::ANIMATION_DRINK);
			$components[] = new UseModifiersComponent(self::CONSUME_DURATION, self::CONSUME_MOVEMENT_MODIFIER);
		}

		if($item instanceof TieredTool){
			$tags[] = "minecraft:is_tool";
			$tags[] = self::resolveTierTag($item->getTier());
			[$tool_tag, $block_tags] = match(true){
				$item instanceof Pickaxe => ["minecraft:is_pickaxe", ["stone", "metal", "minecraft:is_pickaxe_item_destructible"]],
				$item instanceof Axe => ["minecraft:is_axe", ["wood", "pumpkin", "minecraft:is_axe_item_destructible"]],
				$item instanceof Shovel => ["minecraft:is_shovel", ["dirt", "sand", "gravel", "grass", "snow", "minecraft:is_shovel_item_destructible"]],
				$item instanceof Hoe => ["minecraft:is_hoe", ["minecraft:is_hoe_item_destructible"]],
				$item instanceof Sword => ["minecraft:is_sword", ["minecraft:is_sword_item_destructible"]],
				default => [null, []]
			};
			if($tool_tag !== null){
				$tags[] = $tool_tag;
			}
			if(!($item instanceof Sword) && count($block_tags) > 0){
				$tags[] = "minecraft:digger";
				$components[] = new DiggerComponent([new DiggerSpeed(BlockDescriptor::tags($block_tags), $item->getTier()->getBaseEfficiency())]);
			}
		}

		if(count($tags) > 0){
			$components[] = new TagsComponent($tags);
		}
		return $components;
	}

	/**
	 * @return EnchantableComponent::SLOT_*|null
	 */
	private static function resolveEnchantableSlot(Item $item) : ?string{
		return match(true){
			$item instanceof Sword => EnchantableComponent::SLOT_SWORD,
			$item instanceof Pickaxe => EnchantableComponent::SLOT_PICKAXE,
			$item instanceof Axe => EnchantableComponent::SLOT_AXE,
			$item instanceof Shovel => EnchantableComponent::SLOT_SHOVEL,
			$item instanceof Hoe => EnchantableComponent::SLOT_HOE,
			$item instanceof Shears => EnchantableComponent::SLOT_SHEARS,
			$item instanceof Bow => EnchantableComponent::SLOT_BOW,
			$item instanceof FishingRod => EnchantableComponent::SLOT_FISHING_ROD,
			$item instanceof FlintSteel => EnchantableComponent::SLOT_FLINT_AND_STEEL,
			$item instanceof Armor => match($item->getArmorSlot()){
				ArmorInventory::SLOT_HEAD => EnchantableComponent::SLOT_ARMOR_HEAD,
				ArmorInventory::SLOT_CHEST => EnchantableComponent::SLOT_ARMOR_TORSO,
				ArmorInventory::SLOT_LEGS => EnchantableComponent::SLOT_ARMOR_LEGS,
				default => EnchantableComponent::SLOT_ARMOR_FEET
			},
			default => null
		};
	}

	private static function resolveTierTag(ToolTier $tier) : string{
		return match($tier){
			ToolTier::WOOD => "minecraft:wooden_tier",
			ToolTier::GOLD => "minecraft:golden_tier",
			ToolTier::STONE => "minecraft:stone_tier",
			ToolTier::COPPER => "minecraft:copper_tier",
			ToolTier::IRON => "minecraft:iron_tier",
			ToolTier::DIAMOND => "minecraft:diamond_tier",
			ToolTier::NETHERITE => "minecraft:netherite_tier"
		};
	}
}
