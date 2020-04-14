<?php
/**
 * Thumbro for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2020 Ether Creative
 */

namespace ether\thumbro;

use craft\gql\base\Arguments;
use ether\thumbro\gql\ThumbroTypes;
use GraphQL\Type\Definition\Type;

/**
 * Class ThumbroTransformArguments
 *
 * @author  Ether Creative
 * @package ether\thumbro
 */
class ThumbroTransformArguments extends Arguments
{

	public static function getArguments (): array
	{
		return [
			'format' => [
				'name' => 'format',
				'type' => ThumbroTypes::Formats(),
				'description' => 'Format of the created image. If unset (default) it will be the same format as the source image.',
			],
			'trim' => [
				'name' => 'trim',
				'type' => Type::boolean(),
				'description' => 'Removing surrounding space in images can be done using the trim option.',
			],
			'mode' => [
				'name' => 'mode',
				'type' => ThumbroTypes::Modes(),
				'description' => 'The transform mode to use',
			],
			'width' => [
				'name' => 'width',
				'type' => Type::int(),
				'description' => 'Width of the image, in pixels.',
			],
			'height' => [
				'name' => 'height',
				'type' => Type::int(),
				'description' => 'Height of the image, in pixels.',
			],
			'ratio' => [
				'name' => 'ratio',
				'type' => Type::float(),
				'description' => 'An aspect ratio (width/height) that is used to calculate the missing size, if width or height is not provided.',
			],
			'effects' => [
				'name' => 'effects',
				'type' => ThumbroTypes::Effects(),
			],
		];
	}

}
