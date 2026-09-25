<?php

declare(strict_types=1);

namespace kostamax27\kiln\network;

use Closure;
use pocketmine\network\mcpe\convert\BlockStateDictionaryEntry;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

final class SingleProtocolNetworkBridge implements NetworkBridge{

	public function getTypeConverters() : array{
		return [ProtocolInfo::CURRENT_PROTOCOL => TypeConverter::getInstance()];
	}

	public function addTypeConverterCreationListener(Closure $listener) : void{
	}

	public function getProtocolId(NetworkSession $session) : int{
		return ProtocolInfo::CURRENT_PROTOCOL;
	}

	public function getMinimumProtocolId() : int{
		return ProtocolInfo::CURRENT_PROTOCOL;
	}

	public function createBlockStateDictionaryEntry(string $name, array $states, int $meta) : BlockStateDictionaryEntry{
		return new BlockStateDictionaryEntry($name, $states, $meta);
	}
}
