<?php

declare(strict_types=1);

namespace kostamax27\kiln\item;

use pocketmine\item\Item;

final class CustomItem{

	/**
	 * A server-side item type and the client-side items it is shown as.
	 *
	 * @param string $identifier identifier the type was built with.
	 * @param Item $item server-side prototype of the item.
	 * @param non-empty-array<int, CustomItemVariant> $variants keyed by the state ID of the item they represent.
	 */
	public function __construct(
		readonly public string $identifier,
		readonly public Item $item,
		readonly public array $variants
	){}

	/**
	 * Returns the variant a server-side item is shown as, or null if its state has none.
	 */
	public function getVariant(Item $item) : ?CustomItemVariant{
		return $this->variants[$item->getStateId()] ?? null;
	}
}