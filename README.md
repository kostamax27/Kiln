# Kiln
Custom items and blocks for PocketMine-MP.

Runs on [axolotl-pm/PocketMine-MP](https://github.com/axolotl-pm/PocketMine-MP) (1.26.50) and
[kostamax27/PocketMine-MP-NG](https://github.com/kostamax27/PocketMine-MP-NG) (1.20.0 - 1.26.50).

## Installation
```yaml
depend: [Kiln]
load: STARTUP
```
Register items and blocks in `onLoad()`. Blocks cannot be registered later.
```php
use kostamax27\kiln\Loader as KilnLoader;

$kiln = $this->getServer()->getPluginManager()->getPlugin("Kiln");
$kiln instanceof KilnLoader || throw new RuntimeException("Kiln is not loaded");
```

## Items
```php
$kiln->getItemRegistry()->register(CustomItemBuilder::create("myplugin:ruby_sword", new Sword(new ItemIdentifier(ItemTypeIds::newId()), "Ruby Sword", ToolTier::DIAMOND, [ItemEnchantmentTags::SWORD]), "myplugin_ruby_sword")
	->setCreativeInfo(CreativeInfo::equipment(CreativeInfo::GROUP_SWORD))
	->addComponent(new CooldownComponent("myplugin:sword", 1.5, CooldownComponent::TYPE_ATTACK))
	->build());
```
- Derived from the item: name, stack size, durability, damage, armor, food, fuel, cooldown, enchantability and tool
  tags. `addComponent()` overrides derived components of the same name;
  `setComponentResolver(NullItemComponentResolver::instance())` turns derivation off.
- The third argument of `create()` is the icon: a key of `texture_data` in your resource pack.
- The item is saved as `myplugin:ruby_sword`, can be given with `/give` and is added to the creative inventory.

### Variants
Custom items have no meta. An item that describes state in `describeState()` is shown to clients as one item per
state, and stays one item type on the server:
```php
final class Gem extends Item{

	private GemType $type = GemType::RUBY;

	protected function describeState(RuntimeDataDescriber $w) : void{
		$w->enum($this->type);
	}

	public function getType() : GemType{
		return $this->type;
	}

	public function setType(GemType $type) : self{
		$this->type = $type;
		return $this;
	}
}

$kiln->getItemRegistry()->register(CustomItemBuilder::create("myplugin:gem", new Gem(new ItemIdentifier(ItemTypeIds::newId()), "Gem"), "myplugin_gem")
	->addVariantComponents(static fn(Gem $gem) : array => [new DisplayNameComponent(ucfirst(strtolower($gem->getType()->name)))])
	->build());
```
- Variants are named `{identifier}_{value}` with icon `{icon}_{value}`: here `myplugin:gem_ruby` with `myplugin_gem_ruby`.
  Enum cases become lowercase names, ints their value, booleans `true` or `false`. `setVariantNames()` replaces this.
- `addComponent()` applies to every variant, `addVariantComponents()` per variant. `addComponent(new
  IconComponent("myplugin_gem"))` gives all variants one icon.
- `get()` returns the definition. Create items with `createItem()`, which takes a variant identifier or the
  registered one, and clone before changing state:
```php
$gem = $kiln->getItemRegistry()->createItem("myplugin:gem");
$player->getInventory()->addItem((clone $gem)->setType(GemType::RUBY), (clone $gem)->setType(GemType::SAPPHIRE));
```

### Vanilla items
```php
$kiln->getItemRegistry()->override(CustomItemBuilder::override(VanillaItems::IRON_SWORD(), "myplugin_iron_sword")
	->addComponent(new CooldownComponent("myplugin:sword", 1.5, CooldownComponent::TYPE_ATTACK), new GlintComponent())
	->build());
```
Only the client definition is replaced; the numeric ID and server-side behaviour stay. The name defaults to the
vanilla translation (`item.iron_sword.name`). Block items cannot be overridden.

## Blocks
```php
$id = BlockTypeIds::newId();
$kiln->getBlockRegistry()->register(CustomBlockBuilder::create("myplugin:ruby_ore", static fn() : Block => new Opaque(new BlockIdentifier($id), "Ruby Ore", new BlockTypeInfo(BlockBreakInfo::pickaxe(3.0))))
	->setCreativeInfo(CreativeInfo::nature(CreativeInfo::GROUP_ORE))
	->addComponent(new GeometryComponent(), MaterialInstancesComponent::all(new MaterialInstance("myplugin_ruby_ore")))
	->addTag("stone", "minecraft:is_pickaxe_item_destructible")
	->build());
```
- The factory also runs on async workers that encode chunks: it must be a `static fn` that captures only scalars and
  returns the same block type every call, so allocate the type ID outside of it.
- Derived from the block: name, break time (hardness), friction, light emission and light dampening.
- `GeometryComponent` and `MaterialInstancesComponent` are required together. Blocks reacting to being used need
  `CustomComponentsComponent`, otherwise clients do not report interactions with them.

### States
Kiln reads states from `describeBlockItemState()` and `describeBlockOnlyState()`. Name one state per describing call,
in the order PocketMine describes them (item state first):
```php
final class RubyLamp extends Opaque{
	use HorizontalFacingTrait;

	private bool $lit = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->bool($this->lit);
	}
}

$kiln->getBlockRegistry()->register(CustomBlockBuilder::create("myplugin:ruby_lamp", static fn() : Block => new RubyLamp(new BlockIdentifier($id), "Ruby Lamp", new BlockTypeInfo(BlockBreakInfo::instant())))
	->setStateNames("myplugin:facing", "myplugin:lit")
	->addComponent(new GeometryComponent(), MaterialInstancesComponent::all(new MaterialInstance("myplugin_lamp")))
	->addPermutation(Permutation::when([StateCondition::bool("myplugin:lit", true)], new LightEmissionComponent(15)))
	->build());
```
| Describing call                                         | Block property                              |
|---------------------------------------------------------|---------------------------------------------|
| `int()`, `boundedInt()`, `boundedIntAuto()`             | int, min to max                             |
| `bool()`                                                | bool                                        |
| `horizontalFacing()`, `facing()`, `facingExcept()`      | `Facing::toString()`: `north`, `south`, ... |
| `axis()`, `horizontalAxis()`                            | `Axis::toString()`: `x`, `y`, `z`           |
| `enum()`                                                | lowercase case name                         |
| `facingFlags()`, `horizontalFacingFlags()`, `enumSet()` | one bool per face or case: `{name}_{face}`  |

`Permutation::when()` takes `StateCondition::int()`, `::bool()`, `::facing()`, `::axis()` and `::enum()`, checked
against the properties when the block is built. `Permutation::molang()` takes a raw condition. Every state is written
and read back before registering; a block that fails is rejected without touching PocketMine's registries.

## Multi-protocol plugins
Kiln detects multi-protocol support built into the server. A plugin that adds it on its own keeps its own
`TypeConverter` per protocol and tracks client protocols itself, so it needs an adapter implementing
[`NetworkBridge`](src/kostamax27/kiln/network/NetworkBridge.php): the converters Kiln injects items and blocks into, and
the protocol of a session. Set it before Kiln is enabled, e.g. in the `onLoad()` of a plugin depending on Kiln:
```php
$kiln->setNetworkBridge(MyMultiVersionBridge::class);
```

## Components
Item components are in [`src/kostamax27/kiln/item/component`](src/kostamax27/kiln/item/component), block components in
[`src/kostamax27/kiln/block/component`](src/kostamax27/kiln/block/component). Each component checks the client protocol when written and
leaves out what older clients do not understand, down to single fields. Experiments are never enabled, and features
that still require one are not supported.

## Resource pack
Icons and block textures are keys of `texture_data` in `textures/item_texture.json` and
`textures/terrain_texture.json`. Vanilla keys holding an array (`sword`, `axe`, `dye_powder`) cannot be used: many
vanilla items, such as `iron_sword`, only exist as an entry of one. Point a key of your own at the vanilla file:
```json
"myplugin_iron_sword": {
	"textures": "textures/items/iron_sword"
}
```
