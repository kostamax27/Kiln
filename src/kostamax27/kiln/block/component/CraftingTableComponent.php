<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component;

use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use function array_map;

final class CraftingTableComponent implements BlockComponent{

	public const NAME = "minecraft:crafting_table";
	public const GRID_SIZE = 3; // the only size clients support

	/**
	 * Opens a 3x3 crafting grid when the block is used.
	 *
	 * @param string $table_name title of the crafting window, literal or a translation key.
	 * @param non-empty-list<string> $crafting_tags recipe tags usable at this table, e.g. "crafting_table".
	 */
	public function __construct(
		readonly public string $table_name,
		readonly public array $crafting_tags = ["crafting_table"]
	){
		TypeValidator::validateText("Crafting table name", $table_name);
		foreach(TypeValidator::validateNonEmptyList("Crafting tags", $crafting_tags) as $tag){
			TypeValidator::validateTag("Crafting tag", $tag);
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, int $protocol_id) : void{
		$components->setTag(self::NAME, CompoundTag::create()
			->setTag("crafting_tags", new ListTag(array_map(static fn(string $tag) : StringTag => new StringTag($tag), $this->crafting_tags), NBT::TAG_String))
			->setInt("grid_size", self::GRID_SIZE)
			->setString("table_name", $this->table_name));
	}
}
