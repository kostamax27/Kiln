<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component\value;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;
use function sprintf;

final class RepairItem{

	/**
	 * Repairs a fixed amount of durability.
	 *
	 * @param non-empty-list<ItemDescriptor> $items items that repair the owning item.
	 * @param float $amount durability restored per item.
	 */
	public static function amount(array $items, float $amount) : self{
		TypeValidator::validateFloat("Repair amount", $amount, 0.0);
		return new self($items, sprintf("%F", $amount));
	}

	/**
	 * Repairs an amount computed by a Molang expression.
	 *
	 * @param non-empty-list<ItemDescriptor> $items items that repair the owning item.
	 * @param string $expression e.g. "query.max_durability * 0.25".
	 */
	public static function expression(array $items, string $expression) : self{
		return new self($items, TypeValidator::validateText("Repair amount expression", $expression));
	}

	/**
	 * @param non-empty-list<ItemDescriptor> $items
	 */
	private function __construct(
		readonly public array $items,
		readonly public string $repair_amount
	){
		TypeValidator::validateNonEmptyList("Repair items", $items);
	}

	public function toNbt() : CompoundTag{
		return CompoundTag::create()
			->setTag("items", new ListTag(array_map(static fn(ItemDescriptor $item) : CompoundTag => $item->toNbt(), $this->items), NBT::TAG_Compound))
			->setString("repair_amount", $this->repair_amount);
	}
}
