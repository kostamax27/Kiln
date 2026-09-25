<?php

declare(strict_types=1);

namespace kostamax27\kiln\network;

use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkSession;
use ReflectionClass;

final class NetworkBridgeFactory{

	public static function detect() : NetworkBridge{
		$converter = new ReflectionClass(TypeConverter::class);
		if(
			$converter->hasMethod("addCreationListener") &&
			$converter->hasMethod("getAll") &&
			(new ReflectionClass(NetworkSession::class))->hasMethod("getProtocolId")
		){
			return new MultiProtocolNetworkBridge();
		}
		return new SingleProtocolNetworkBridge();
	}
}