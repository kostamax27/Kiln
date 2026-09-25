<?php

declare(strict_types=1);

namespace kostamax27\kiln\network;

use pocketmine\nbt\tag\Tag;
use pocketmine\network\mcpe\convert\TypeConverter;
use function count;

final class CustomBlockStateMapper{

	/** @var array<string, list<array<string, Tag>>> */
	private array $blocks = [];

	public function __construct(
		readonly private NetworkBridge $bridge
	){
		$bridge->addTypeConverterCreationListener(function(int $protocol_id, TypeConverter $converter) : void{
			$this->inject($converter, $this->blocks);
		});
	}

	/**
	 * @param array<string, list<array<string, Tag>>> $blocks
	 */
	public function register(array $blocks) : void{
		foreach($this->bridge->getTypeConverters() as $converter){
			$this->inject($converter, $blocks);
		}
		$this->blocks += $blocks;
	}

	/**
	 * @param array<string, list<array<string, Tag>>> $blocks
	 */
	private function inject(TypeConverter $converter, array $blocks) : void{
		$entries = [];
		foreach($blocks as $identifier => $states){
			foreach($states as $meta => $state){
				$entries[] = $this->bridge->createBlockStateDictionaryEntry($identifier, $state, $meta);
			}
		}
		if(count($entries) > 0){
			BlockStateDictionaryInjector::inject($converter->getBlockTranslator(), $entries);
		}
	}
}