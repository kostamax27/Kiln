<?php

declare(strict_types=1);

namespace kostamax27\kiln\block\component\value;

use kostamax27\kiln\util\TypeValidator;

final class MaterialInstance{

	public const RENDER_METHOD_OPAQUE = "opaque";
	public const RENDER_METHOD_DOUBLE_SIDED = "double_sided";
	public const RENDER_METHOD_BLEND = "blend";
	public const RENDER_METHOD_ALPHA_TEST = "alpha_test";
	public const RENDER_METHOD_ALPHA_TEST_SINGLE_SIDED = "alpha_test_single_sided";
	public const RENDER_METHOD_BLEND_TO_OPAQUE = "blend_to_opaque";
	public const RENDER_METHOD_ALPHA_TEST_TO_OPAQUE = "alpha_test_to_opaque";
	public const RENDER_METHOD_ALPHA_TEST_SINGLE_SIDED_TO_OPAQUE = "alpha_test_single_sided_to_opaque";

	public const TINT_METHOD_NONE = "none";
	public const TINT_METHOD_DEFAULT_FOLIAGE = "default_foliage";
	public const TINT_METHOD_BIRCH_FOLIAGE = "birch_foliage";
	public const TINT_METHOD_EVERGREEN_FOLIAGE = "evergreen_foliage";
	public const TINT_METHOD_DRY_FOLIAGE = "dry_foliage";
	public const TINT_METHOD_GRASS = "grass";
	public const TINT_METHOD_WATER = "water";

	public const TINT_METHODS = [self::TINT_METHOD_NONE, self::TINT_METHOD_DEFAULT_FOLIAGE, self::TINT_METHOD_BIRCH_FOLIAGE, self::TINT_METHOD_EVERGREEN_FOLIAGE, self::TINT_METHOD_DRY_FOLIAGE, self::TINT_METHOD_GRASS, self::TINT_METHOD_WATER];

	private const RENDER_METHODS = [self::RENDER_METHOD_OPAQUE, self::RENDER_METHOD_DOUBLE_SIDED, self::RENDER_METHOD_BLEND, self::RENDER_METHOD_ALPHA_TEST, self::RENDER_METHOD_ALPHA_TEST_SINGLE_SIDED, self::RENDER_METHOD_BLEND_TO_OPAQUE, self::RENDER_METHOD_ALPHA_TEST_TO_OPAQUE, self::RENDER_METHOD_ALPHA_TEST_SINGLE_SIDED_TO_OPAQUE];

	/**
	 * @param string $texture texture key from the resource pack's terrain_texture.json.
	 * @param self::RENDER_METHOD_* $render_method
	 * @param bool $face_dimming whether faces are dimmed depending on their direction.
	 * @param float $ambient_occlusion exponent of ambient occlusion shading, 0 to disable, [0, 10].
	 * @param bool $isotropic whether face UVs are randomly rotated.
	 * @param self::TINT_METHOD_* $tint_method
	 */
	public function __construct(
		readonly public string $texture,
		readonly public string $render_method = self::RENDER_METHOD_OPAQUE,
		readonly public bool $face_dimming = true,
		readonly public float $ambient_occlusion = 1.0,
		readonly public bool $isotropic = false,
		readonly public string $tint_method = self::TINT_METHOD_NONE
	){
		TypeValidator::validateTexture("Material texture", $texture);
		TypeValidator::validateChoice("Material render method", $render_method, self::RENDER_METHODS);
		TypeValidator::validateFloat("Material ambient occlusion", $ambient_occlusion, 0.0, 10.0);
		TypeValidator::validateChoice("Material tint method", $tint_method, self::TINT_METHODS);
	}
}