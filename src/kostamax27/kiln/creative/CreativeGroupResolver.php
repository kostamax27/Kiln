<?php

declare(strict_types=1);

namespace kostamax27\kiln\creative;

use pocketmine\inventory\CreativeCategory;
use pocketmine\inventory\CreativeGroup;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\lang\Translatable;
use function is_string;
use function str_starts_with;

final class CreativeGroupResolver{

	/** @var array<string, CreativeGroup> */
	private array $groups;

	public function __construct(
		readonly private CreativeInventory $inventory
	){}

	public function resolve(CreativeCategory $category, string $name, Item $icon) : CreativeGroup{
		if(!isset($this->groups)){
			$this->groups = [];
			foreach($this->inventory->getAllEntries() as $entry){
				$group = $entry->getGroup();
				if($group !== null){
					$group_name = $group->getName();
					$this->groups[self::key($entry->getCategory(), is_string($group_name) ? $group_name : $group_name->getText())] ??= $group;
				}
			}
		}
		return $this->groups[self::key($category, $name)] ??= new CreativeGroup(
			str_starts_with($name, "itemGroup.") ? new Translatable($name) : $name,
			clone $icon
		);
	}

	private static function key(CreativeCategory $category, string $name) : string{
		return "{$category->name}:{$name}";
	}
}
