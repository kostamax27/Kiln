<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\resolver;

use kostamax27\kiln\item\component\ItemComponent;
use kostamax27\kiln\item\property\ItemProperty;
use pocketmine\item\Item;

interface ItemComponentResolver{

	/**
	 * Derives client-side components from a server-side item so that both sides agree on behaviour such as
	 * stack size, durability and food values.
	 *
	 * @param string $identifier identifier the item is being registered with.
	 * @param Item $item server-side prototype of the item.
	 * @return list<ItemComponent>
	 */
	public function resolveComponents(string $identifier, Item $item) : array;

	/**
	 * Derives legacy item properties from a server-side item.
	 *
	 * @param string $identifier identifier the item is being registered with.
	 * @param Item $item server-side prototype of the item.
	 * @return list<ItemProperty>
	 */
	public function resolveProperties(string $identifier, Item $item) : array;
}
