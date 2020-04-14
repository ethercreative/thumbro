<?php
/**
 * Thumbro for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2020 Ether Creative
 */

namespace ether\thumbro;

use craft\elements\Asset;
use craft\gql\base\Directive;
use craft\gql\GqlEntityRegistry;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\Directive as GqlDirective;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class ThumbroDirective
 *
 * @author  Ether Creative
 * @package ether\thumbro
 */
class ThumbroDirective extends Directive
{

	public function __construct(array $config)
	{
		$args = &$config['args'];

		foreach ($args as &$argument) {
			$argument = new FieldArgument($argument);
		}

		parent::__construct($config);
	}

	/**
	 * @inheritDoc
	 */
	public static function create (): GqlDirective
	{
		if ($type = GqlEntityRegistry::getEntity(self::name()))
			return $type;

		$type = GqlEntityRegistry::createEntity(static::name(), new self([
			'name' => static::name(),
			'locations' => [
				DirectiveLocation::FIELD,
			],
			// TODO: Make args an array of transform arguments
			'args' => ThumbroTransformArguments::getArguments(),
			'description' => 'Transform an image using Thumbro',
		]));

		return $type;
	}

	/**
	 * @inheritDoc
	 */
	public static function name (): string
	{
		return 'thumbro';
	}

	/**
	 * @inheritDoc
	 */
	public static function apply (
		$source, $value, array $transform, ResolveInfo $resolveInfo
	) {
		$onAssetElement = $source === null && $value instanceof Asset;
		$onAssetElementList = $source === null && is_array($value) && !empty($value);
		$onApplicableAssetField = $source instanceof Asset && $resolveInfo->fieldName === 'url';

		if (!($onAssetElement || $onAssetElementList || $onApplicableAssetField) || empty($arguments) ) {
			return $value;
		}

		$variable = new Variable();

		// If this directive is applied to an entire Asset
		if ($onAssetElement || $onApplicableAssetField)
			return $variable->img($value, $transform);

		if ($onAssetElementList) {
			$res = [];

			foreach ($value as &$asset) {
				// If this somehow ended up being a mix of elements, don't explicitly fail, just set the transform on the asset elements
				if ($asset instanceof Asset) {
					$res[] = $variable->img($asset, $transform);
				} else {
					$res[] = $asset;
				}
			}

			return $res;
		}

		return $value;
	}

}
