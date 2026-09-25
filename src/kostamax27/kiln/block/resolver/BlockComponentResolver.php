<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\resolver;

use kostamax27\kiln\block\component\BlockComponent;
use pocketmine\block\Block;

interface BlockComponentResolver{

	/**
	 * Derives client-side components from a server-side block so that both sides agree on behaviour such
	 * as break time and light.
	 *
	 * @param string $identifier identifier the block is being registered with.
	 * @param Block $block server-side prototype of the block.
	 * @return list<BlockComponent>
	 */
	public function resolve(string $identifier, Block $block) : array;
}