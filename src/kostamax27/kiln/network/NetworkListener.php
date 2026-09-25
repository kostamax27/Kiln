<?php

declare(strict_types=1);

namespace kostamax27\kiln\network;

use kostamax27\kiln\block\CustomBlockRegistry;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AvailableActorIdentifiersPacket;
use pocketmine\network\mcpe\protocol\ItemRegistryPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\BlockPaletteEntry;
use function array_merge;
use function count;
use function hash;
use function reset;
use function strcmp;
use function usort;

final class NetworkListener implements Listener{

	/** @var array<int, list<BlockPaletteEntry>> */
	private array $palettes = [];

	public function __construct(
		readonly private NetworkBridge $bridge,
		readonly private CustomItemTypeMapper $item_type_mapper,
		readonly private CustomBlockRegistry $block_registry
	){}

	/**
	 * @priority HIGHEST
	 */
	public function onDataPacketSend(DataPacketSendEvent $event) : void{
		$packets = [];
		$update = false;
		foreach($event->getPackets() as $packet){
			if($packet instanceof StartGamePacket){
				if(($session = self::getSingleTarget($event)) !== null){
					$packet->blockPalette = $this->palettes[$protocol_id = $this->bridge->getProtocolId($session)] ??= self::sortPalette(array_merge($packet->blockPalette, $this->block_registry->getPaletteEntries($protocol_id)));
				}
			}elseif($packet instanceof AvailableActorIdentifiersPacket && ($legacy = $this->createLegacyItemComponentPacket($event)) !== null){
				$packets[] = $legacy;
				$update = true;
			}
			$packets[] = $packet;
		}
		if($update){
			$event->setPackets($packets);
		}
	}

	private function createLegacyItemComponentPacket(DataPacketSendEvent $event) : ?ItemRegistryPacket{
		$session = self::getSingleTarget($event);
		if($session === null || ($protocol_id = $this->bridge->getProtocolId($session)) >= ProtocolVersions::V1_21_60){
			return null;
		}
		$entries = $this->item_type_mapper->createComponentEntries($protocol_id);
		return count($entries) > 0 ? ItemRegistryPacket::create($entries) : null;
	}


	/**
	 * Clients expect data-driven blocks, vanilla and custom alike, in the order of the FNV-1 64-bit hash of their names.
	 *
	 * @param list<BlockPaletteEntry> $entries
	 * @return list<BlockPaletteEntry>
	 */
	private static function sortPalette(array $entries) : array{
		usort($entries, static fn(BlockPaletteEntry $a, BlockPaletteEntry $b) : int => strcmp(hash("fnv164", $a->getName()), hash("fnv164", $b->getName())));
		return $entries;
	}

	private static function getSingleTarget(DataPacketSendEvent $event) : ?NetworkSession{
		$targets = $event->getTargets();
		return count($targets) === 1 ? reset($targets) : null;
	}
}