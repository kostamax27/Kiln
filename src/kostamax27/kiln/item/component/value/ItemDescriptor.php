<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component\value;

use kostamax27\kiln\util\Molang;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\CompoundTag;

final class ItemDescriptor{

	/**
	 * Describes an item by its identifier.
	 *
	 * @param string $name item identifier such as "minecraft:iron_ingot".
	 */
	public static function name(string $name) : self{
		return new self(CompoundTag::create()->setString("name", TypeValidator::validateIdentifier("Item descriptor name", $name)));
	}

	/**
	 * Describes every item carrying any of the given item tags.
	 *
	 * @param list<string> $tags item tags such as "minecraft:planks".
	 */
	public static function tags(array $tags) : self{
		return new self(CompoundTag::create()->setString("tags", Molang::anyTag("Item descriptor tags", $tags)));
	}

	private function __construct(
		readonly private CompoundTag $nbt
	){}

	public function toNbt() : CompoundTag{
		return clone $this->nbt;
	}
}