<?php

declare(strict_types=1);

namespace kostamax27\kiln\network;

use InvalidArgumentException;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\network\mcpe\convert\BlockStateDictionary;
use pocketmine\network\mcpe\convert\BlockStateDictionaryEntry;
use pocketmine\network\mcpe\convert\BlockTranslator;
use pocketmine\utils\AssumptionFailedError;
use ReflectionClass;
use function array_keys;
use function array_shift;
use function count;
use function hash;
use function ksort;
use function strcmp;
use function uksort;

final class BlockStateDictionaryInjector{

	private function __construct(){
	}

	/**
	 * @param list<BlockStateDictionaryEntry> $entries
	 */
	public static function inject(BlockTranslator $translator, array $entries) : void{
		$dictionary = $translator->getBlockStateDictionary();

		$custom = [];
		foreach($entries as $entry){
			$custom[$entry->getStateName()][] = $entry;
		}

		$existing = [];
		foreach($dictionary->getStates() as $entry){
			if(isset($custom[$name = $entry->getStateName()])){
				$existing[$name][] = $entry;
			}
		}
		foreach($existing as $name => $existing_entries){
			self::encodeStates($existing_entries) === self::encodeStates($custom[$name]) || throw new InvalidArgumentException("Block state dictionary already contains a different definition of block \"{$name}\", protocols sharing it need identical states");
			unset($custom[$name]);
		}
		if(count($custom) === 0){
			return;
		}

		uksort($custom, static fn(string $a, string $b) : int => strcmp(hash("fnv164", $a), hash("fnv164", $b)));
		$pending = array_keys($custom);

		$sorted = [];
		$previous = null;
		foreach($dictionary->getStates() as $entry){
			$name = $entry->generateStateData()->getName();
			!isset($custom[$name]) && !isset($custom[$entry->getStateName()]) || throw new InvalidArgumentException("Block state dictionary already contains block \"{$name}\"");
			if($name !== $previous){
				while(count($pending) > 0 && strcmp(hash("fnv164", $pending[0]), hash("fnv164", $name)) < 0){
					foreach($custom[array_shift($pending)] as $custom_entry){
						$sorted[] = $custom_entry;
					}
				}
				$previous = $name;
			}
			$sorted[] = $entry;
		}
		foreach($pending as $name){
			foreach($custom[$name] as $custom_entry){
				$sorted[] = $custom_entry;
			}
		}

		$fresh = new BlockStateDictionary($sorted);
		$reflection = new ReflectionClass(BlockStateDictionary::class);
		foreach($reflection->getProperties() as $property){
			if(!$property->isStatic()){
				$property->setValue($dictionary, $property->getValue($fresh));
			}
		}

		$fallback = $dictionary->lookupStateIdFromData(BlockStateData::current(BlockTypeNames::INFO_UPDATE, [])) ?? throw new AssumptionFailedError(BlockTypeNames::INFO_UPDATE . " should always exist");
		$translator_reflection = new ReflectionClass(BlockTranslator::class);
		$translator_reflection->getProperty("networkIdCache")->setValue($translator, []);
		$translator_reflection->getProperty("fallbackStateId")->setValue($translator, $fallback);
	}

	/**
	 * @param list<BlockStateDictionaryEntry> $entries
	 * @return array<int, string>
	 */
	private static function encodeStates(array $entries) : array{
		$states = [];
		foreach($entries as $entry){
			$states[$entry->getMeta()] = $entry->getRawStateProperties();
		}
		ksort($states);
		return $states;
	}
}
