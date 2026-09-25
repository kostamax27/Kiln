<?php

declare(strict_types=1);

namespace kostamax27\kiln\block;

use BadMethodCallException;
use InvalidArgumentException;
use kostamax27\kiln\creative\CreativeGroupResolver;
use kostamax27\kiln\network\BlockItemIdMapInjector;
use kostamax27\kiln\network\CustomBlockStateMapper;
use kostamax27\kiln\network\CustomItemTypeMapper;
use kostamax27\kiln\network\NetworkBridge;
use pocketmine\block\Block;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\network\mcpe\protocol\types\BlockPaletteEntry;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\scheduler\AsyncPool;
use function count;

final class CustomBlockRegistry{

	public const FIRST_BLOCK_ID = 10000;
	public const LAST_BLOCK_ID = 33023;
	public const ITEM_TYPE_VERSION_NONE = 2;

	/** @var array<string, CustomBlock> */
	private array $blocks = [];

	/** @var array<string, int> */
	private array $block_ids = [];

	/** @var array<int, CustomBlock> */
	private array $blocks_by_type_id = [];

	/** @var array<string, non-empty-list<array<string, Tag>>> */
	private array $states = [];

	/** @var array<int, list<BlockPaletteEntry>> */
	private array $palette_entries = [];

	private bool $frozen = false;

	public function __construct(
		readonly private CustomItemTypeMapper $item_type_mapper,
		readonly private CreativeGroupResolver $creative_groups
	){}

	/**
	 * Registers a custom block so that it can be placed, saved, parsed by commands such as /give and
	 * rendered by clients.
	 */
	public function register(CustomBlock $block) : void{
		$identifier = $block->identifier;
		!$this->frozen || throw new BadMethodCallException("Cannot register block \"{$identifier}\": blocks must be registered during the onLoad() phase of your plugin");
		!isset($this->blocks[$identifier]) || throw new InvalidArgumentException("Custom block \"{$identifier}\" is already registered");
		$block_id = self::FIRST_BLOCK_ID + count($this->blocks);
		$block_id <= self::LAST_BLOCK_ID || throw new InvalidArgumentException("Cannot register more than " . (self::LAST_BLOCK_ID - self::FIRST_BLOCK_ID + 1) . " custom blocks");
		$parser = StringToItemParser::getInstance();
		$parser->parse($identifier) === null || throw new InvalidArgumentException("Alias \"{$identifier}\" is already registered in " . StringToItemParser::class);

		$states = BlockRuntimeRegistrar::register($identifier, $block->block, $block->state_names, true);
		$prototype = clone $block->block;
		$parser->registerBlock($identifier, static fn() : Block => clone $prototype);

		$this->blocks[$identifier] = $block;
		$this->block_ids[$identifier] = $block_id;
		$this->blocks_by_type_id[$prototype->getTypeId()] = $block;
		$this->states[$identifier] = $states;

		$category = $block->creative_info->toCreativeCategory();
		if($category !== null){
			$item = $prototype->asItem();
			$group = $block->creative_info->group === null ? null : $this->creative_groups->resolve($category, $block->creative_info->group, $item);
			CreativeInventory::getInstance()->add($item, $category, $group);
		}
	}

	/**
	 * Publishes every registered block to the network layer and to async workers, after which no more
	 * blocks can be registered.
	 *
	 * @internal
	 */
	public function freeze(AsyncPool $pool, NetworkBridge $bridge) : void{
		!$this->frozen || throw new BadMethodCallException("Block registry is already frozen");
		$this->frozen = true;
		if(count($this->blocks) === 0){
			return;
		}

		(new CustomBlockStateMapper($bridge))->register($this->states);
		$block_item_map = BlockItemIdMap::getInstance();
		$entries = [];
		foreach($this->blocks as $identifier => $block){
			BlockItemIdMapInjector::inject($block_item_map, $identifier);
			$item_id = 255 - $this->block_ids[$identifier];
			$this->item_type_mapper->register($identifier, static fn(int $protocol_id) : ItemTypeEntry => new ItemTypeEntry($identifier, $item_id, false, self::ITEM_TYPE_VERSION_NONE, new CacheableNbt(CompoundTag::create())));
			$entries[] = [$identifier, $block->factory, $block->state_names];
		}
		$bridge_class = $bridge::class;
		$pool->addWorkerStartHook(static function(int $worker) use($pool, $entries, $bridge_class) : void{
			$pool->submitTaskToWorker(new RegisterCustomBlocksTask($entries, $bridge_class), $worker);
		});
	}

	/**
	 * Returns whether Kiln has been enabled, after which no more blocks can be registered.
	 */
	public function isFrozen() : bool{
		return $this->frozen;
	}

	/**
	 * Returns the block definitions sent to clients of the given protocol.
	 *
	 * @return list<BlockPaletteEntry>
	 */
	public function getPaletteEntries(int $protocol_id) : array{
		if(!isset($this->palette_entries[$protocol_id])){
			$entries = [];
			foreach($this->blocks as $identifier => $block){
				$entries[] = new BlockPaletteEntry($identifier, new CacheableNbt($block->encode($protocol_id, $this->block_ids[$identifier])));
			}
			$this->palette_entries[$protocol_id] = $entries;
		}
		return $this->palette_entries[$protocol_id];
	}

	/**
	 * Returns the custom block registered with the given identifier.
	 */
	public function get(string $identifier) : CustomBlock{
		return $this->blocks[$identifier] ?? throw new InvalidArgumentException("Custom block \"{$identifier}\" is not registered");
	}

	/**
	 * Returns the custom block registered with the given identifier, or null if there is none.
	 */
	public function getNullable(string $identifier) : ?CustomBlock{
		return $this->blocks[$identifier] ?? null;
	}

	/**
	 * Returns the custom block definition the given block instance belongs to, if any.
	 */
	public function getFromBlock(Block $block) : ?CustomBlock{
		return $this->blocks_by_type_id[$block->getTypeId()] ?? null;
	}

	/**
	 * Returns a new instance of a registered custom block.
	 */
	public function createBlock(string $identifier) : Block{
		return clone $this->get($identifier)->block;
	}

	/**
	 * Returns every registered custom block keyed by identifier.
	 *
	 * @return array<string, CustomBlock>
	 */
	public function getAll() : array{
		return $this->blocks;
	}
}