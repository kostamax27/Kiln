<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component;

use kostamax27\kiln\network\ProtocolVersions;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use function array_map;
use function array_unique;
use function array_values;

final class TagsComponent implements ItemComponent{

	public const NAME = "minecraft:tags";

	/** @var non-empty-list<string> */
	readonly public array $tags;

	/**
	 * @param non-empty-list<string> $tags item tags such as "minecraft:is_sword".
	 */
	public function __construct(array $tags){
		foreach(TypeValidator::validateNonEmptyList("Item tags", $tags) as $tag){
			TypeValidator::validateTag("Item tag", $tag);
		}
		$this->tags = array_values(array_unique($tags));
	}

	public function getName() : string{
		return self::NAME;
	}

	public function write(CompoundTag $components, CompoundTag $properties, int $protocol_id) : void{
		$components->setTag("item_tags", $this->createList());
		if($protocol_id >= ProtocolVersions::V1_20_50){
			$components->setTag(self::NAME, CompoundTag::create()->setTag("tags", $this->createList()));
		}
	}

	/**
	 * @return ListTag<StringTag>
	 */
	private function createList() : ListTag{
		return new ListTag(array_map(static fn(string $tag) : StringTag => new StringTag($tag), $this->tags), NBT::TAG_String);
	}
}