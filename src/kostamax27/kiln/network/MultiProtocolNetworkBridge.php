<?php

declare(strict_types=1);

namespace kostamax27\kiln\network;

use Closure;
use pocketmine\network\mcpe\convert\BlockStateDictionaryEntry;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use function min;

final class MultiProtocolNetworkBridge implements NetworkBridge{

	public function getTypeConverters() : array{
		$converters = TypeConverter::getAll();
		$converters[ProtocolInfo::CURRENT_PROTOCOL] ??= TypeConverter::getInstance(ProtocolInfo::CURRENT_PROTOCOL);
		return $converters;
	}

	public function addTypeConverterCreationListener(Closure $listener) : void{
		TypeConverter::addCreationListener(static function(TypeConverter $converter) use($listener) : void{
			$listener($converter->getProtocolId(), $converter);
		});
	}

	public function getProtocolId(NetworkSession $session) : int{
		return $session->getProtocolId();
	}

	public function getMinimumProtocolId() : int{
		return min(ProtocolInfo::ACCEPTED_PROTOCOL);
	}

	public function createBlockStateDictionaryEntry(string $name, array $states, int $meta) : BlockStateDictionaryEntry{
		return new BlockStateDictionaryEntry($name, $states, $meta, null);
	}
}