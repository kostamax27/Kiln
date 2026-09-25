<?php

declare(strict_types=1);

namespace kostamax27\kiln;

use BadMethodCallException;
use InvalidArgumentException;
use kostamax27\kiln\block\CustomBlockRegistry;
use kostamax27\kiln\creative\CreativeGroupResolver;
use kostamax27\kiln\item\CustomItemRegistry;
use kostamax27\kiln\network\CustomItemTypeMapper;
use kostamax27\kiln\network\NetworkBridge;
use kostamax27\kiln\network\NetworkBridgeFactory;
use kostamax27\kiln\network\NetworkListener;
use kostamax27\kiln\network\ProtocolVersions;
use pocketmine\inventory\CreativeInventory;
use pocketmine\plugin\PluginBase;
use ReflectionClass;
use function count;
use function is_a;

final class Loader extends PluginBase{

	/** @var class-string<NetworkBridge>|null */
	private ?string $network_bridge_class = null;

	private NetworkBridge $network_bridge;
	private CustomItemTypeMapper $item_type_mapper;
	private CustomItemRegistry $item_registry;
	private CustomBlockRegistry $block_registry;

	protected function onLoad() : void{
		$this->item_type_mapper = new CustomItemTypeMapper();
		$creative_groups = new CreativeGroupResolver(CreativeInventory::getInstance());
		$this->item_registry = new CustomItemRegistry($this->item_type_mapper, $creative_groups);
		$this->block_registry = new CustomBlockRegistry($this->item_type_mapper, $creative_groups);
	}

	protected function onEnable() : void{
		$this->network_bridge = $this->network_bridge_class !== null ? new $this->network_bridge_class() : NetworkBridgeFactory::detect();
		$this->item_type_mapper->attach($this->network_bridge);
		$this->block_registry->freeze($this->getServer()->getAsyncPool(), $this->network_bridge);
		if(
			count($this->block_registry->getAll()) > 0 ||
			$this->network_bridge->getMinimumProtocolId() < ProtocolVersions::V1_21_60
		){
			$this->getServer()->getPluginManager()->registerEvents(new NetworkListener($this->network_bridge, $this->item_type_mapper, $this->block_registry), $this);
		}
	}

	/**
	 * Replaces the detected network bridge, e.g. for servers where a plugin provides multi-protocol support. Must be
	 * called before Kiln is enabled; async workers create their own instance, so the class takes no arguments.
	 *
	 * @param string $class class implementing NetworkBridge.
	 */
	public function setNetworkBridge(string $class) : void{
		!$this->isEnabled() || throw new BadMethodCallException("The network bridge must be set before " . $this->getName() . " is enabled");
		is_a($class, NetworkBridge::class, true) || throw new InvalidArgumentException("{$class} must implement " . NetworkBridge::class);
		((new ReflectionClass($class))->getConstructor()?->getNumberOfRequiredParameters() ?? 0) === 0 || throw new InvalidArgumentException("{$class} must be constructible without arguments");
		$this->network_bridge_class = $class;
	}

	public function getNetworkBridge() : NetworkBridge{
		return $this->network_bridge ?? throw new BadMethodCallException("The network bridge is chosen when " . $this->getName() . " is enabled");
	}

	public function getItemRegistry() : CustomItemRegistry{
		return $this->item_registry;
	}

	public function getBlockRegistry() : CustomBlockRegistry{
		return $this->block_registry;
	}
}