<?php

declare(strict_types=1);

namespace kostamax27\kiln\block;

use Closure;
use InvalidArgumentException;
use kostamax27\kiln\block\state\BlockProperty;
use kostamax27\kiln\block\state\StateReadDescriber;
use kostamax27\kiln\block\state\StateSchemaDescriber;
use kostamax27\kiln\block\state\StateWriteDescriber;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\nbt\tag\Tag;
use pocketmine\network\mcpe\convert\BlockStateDictionaryEntry;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use ReflectionMethod;
use function count;

final class BlockRuntimeRegistrar{

	/** @var array<class-string<Block>, ReflectionMethod> */
	private static array $describe_block_only_state = [];

	private function __construct(){
	}

	/**
	 * @param list<string> $state_names
	 * @return list<BlockProperty>
	 */
	public static function describeProperties(Block $block, array $state_names) : array{
		$schema = new StateSchemaDescriber($state_names);
		self::describe(clone $block, $schema);
		return $schema->getProperties();
	}

	/**
	 * @param list<string> $state_names
	 * @return non-empty-list<array<string, Tag>>
	 */
	public static function register(string $identifier, Block $block, array $state_names, bool $validate) : array{
		$prototype = clone $block;
		$states = BlockProperty::generateStates(self::describeProperties($prototype, $state_names));
		$block_serializer = GlobalBlockStateHandlers::getSerializer();
		$block_deserializer = GlobalBlockStateHandlers::getDeserializer();
		$block_deserializer->getDeserializerForId($identifier) === null || throw new InvalidArgumentException("Block \"{$identifier}\" already has a deserializer registered");
		$block_serializer->isRegistered($prototype) && throw new InvalidArgumentException("Block type ID {$prototype->getTypeId()} of \"{$identifier}\" already has a serializer registered");

		$serialize = static function(Block $block) use($identifier, $state_names) : BlockStateWriter{
			$writer = BlockStateWriter::create($identifier);
			self::describe($block, new StateWriteDescriber($writer, $state_names));
			return $writer;
		};
		$deserialize = static function(BlockStateReader $in) use($prototype, $state_names) : Block{
			$block = clone $prototype;
			self::describe($block, new StateReadDescriber($in, $state_names));
			return $block;
		};
		if($validate && count($state_names) > 0){
			self::validate($identifier, $prototype, $states, $serialize, $deserialize);
		}

		RuntimeBlockStateRegistry::getInstance()->register($prototype);
		$block_serializer->map($prototype, count($state_names) > 0 ? $serialize : BlockStateData::current($identifier, []));
		$block_deserializer->map($identifier, $deserialize);
		return $states;
	}

	/**
	 * @param non-empty-list<array<string, Tag>> $states
	 * @param Closure(Block) : BlockStateWriter $serialize
	 * @param Closure(BlockStateReader) : Block $deserialize
	 */
	private static function validate(string $identifier, Block $block, array $states, Closure $serialize, Closure $deserialize) : void{
		$declared = [];
		foreach($states as $state){
			$declared[BlockStateDictionaryEntry::encodeStateProperties($state)] = true;
		}
		$state_ids = [];
		foreach($block->generateStatePermutations() as $permutation){
			$state_ids[$permutation->getStateId()] = true;
			$data = $serialize($permutation)->getBlockStateData();
			isset($declared[BlockStateDictionaryEntry::encodeStateProperties($data->getStates())]) || throw new InvalidArgumentException("Block \"{$identifier}\" has a state outside of its Bedrock properties: {$data->toNbt()}");
		}
		foreach($states as $state){
			$data = BlockStateData::current($identifier, $state);
			$reader = new BlockStateReader($data);
			try{
				$read = $deserialize($reader);
				$reader->checkUnreadProperties();
			}catch(BlockStateDeserializeException $e){
				throw new InvalidArgumentException("Block \"{$identifier}\" could not read state {$data->toNbt()}: {$e->getMessage()}", 0, $e);
			}
			isset($state_ids[$read->getStateId()]) || throw new InvalidArgumentException("Block \"{$identifier}\" read state {$data->toNbt()} into a state it cannot be in");
		}
	}

	/**
	 * Runs a describer over the item state, then the block-only state, as PocketMine encodes them.
	 */
	private static function describe(Block $block, RuntimeDataDescriber $describer) : void{
		$block->describeBlockItemState($describer);
		$method = self::$describe_block_only_state[$block::class] ??= new ReflectionMethod($block, "describeBlockOnlyState");
		$method->invoke($block, $describer);
	}
}
