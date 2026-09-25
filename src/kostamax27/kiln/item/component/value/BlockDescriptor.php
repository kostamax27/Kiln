<?php

declare(strict_types=1);

namespace kostamax27\kiln\item\component\value;

use kostamax27\kiln\util\Molang;
use kostamax27\kiln\util\TypeValidator;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use function count;

final class BlockDescriptor{

	/**
	 * Describes a block by its identifier and, optionally, a subset of its states.
	 *
	 * @param string $name block identifier such as "minecraft:farmland".
	 * @param array<string, ByteTag|IntTag|StringTag> $states block states that must match, e.g. ["moisturized_amount" => new IntTag(7)].
	 */
	public static function name(string $name, array $states = []) : self{
		$nbt = CompoundTag::create()->setString("name", TypeValidator::validateIdentifier("Block descriptor name", $name));
		if(count($states) > 0){
			$states_nbt = CompoundTag::create();
			foreach($states as $state => $value){
				$states_nbt->setTag(TypeValidator::validateTag("Block descriptor state name", $state), $value);
			}
			$nbt->setTag("states", $states_nbt);
		}
		return new self($nbt);
	}

	/**
	 * Describes every block carrying any of the given block tags.
	 *
	 * @param list<string> $tags block tags such as "stone" or "minecraft:is_pickaxe_item_destructible".
	 */
	public static function tags(array $tags) : self{
		return new self(CompoundTag::create()->setString("tags", Molang::anyTag("Block descriptor tags", $tags)));
	}

	private function __construct(
		readonly private CompoundTag $nbt
	){}

	public function toNbt() : CompoundTag{
		return clone $this->nbt;
	}
}
