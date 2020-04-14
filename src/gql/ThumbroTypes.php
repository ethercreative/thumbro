<?php
/**
 * Thumbro for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2020 Ether Creative
 */

namespace ether\thumbro\gql;

use craft\events\RegisterGqlTypesEvent;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;

/**
 * Class ThumbroTypes
 *
 * @author  Ether Creative
 * @package ether\thumbro\gql
 */
final class ThumbroTypes
{

	public static function register (RegisterGqlTypesEvent $event)
	{
		$methods = get_class_methods(new self());

		foreach ($methods as $method) if ($method !== 'register')
			$event->types[] = self::{$method}();
	}

	public static function Formats ()
	{
		return new EnumType([
			'name' => 'Format',
			'description' => 'Valid transform formats',
			'values' => [
				'JPG' => [
					'value' => 'jpg',
				],
				'PNG' => [
					'value' => 'png',
				],
				'GIF' => [
					'value' => 'gif',
				],
				'WEBP' => [
					'value' => 'webp',
				],
			],
		]);
	}

	public static function Modes ()
	{
		return new EnumType([
			'name' => 'Mode',
			'description' => 'Transform modes',
			'values' => [
				'CROP' => [
					'value' => 'crop',
					'description' => 'Crops the image to the given size, scaling the image to fill as much as possible of the size.',
				],
				'FIT' => [
					'value' => 'fit',
					'description' => 'Scales the image to fit within the given size while maintaining the aspect ratio of the original image.',
				],
				'STRETCH' => [
					'value' => 'stretch',
					'description' => 'Scales the image to the given size, stretching it if the aspect ratio is different from the original.',
				],
			],
		]);
	}

	public static function Coordinates ()
	{
		return new ObjectType([
			'name' => 'Coordinates',
			'description' => 'Percentage coordinates of an image',
			'fields' => [
				'x' => [
					'name' => 'x',
					'type' => Type::float(),
				],
				'y' => [
					'name' => 'y',
					'type' => Type::float(),
				],
			],
		]);
	}

	public static function Position ()
	{
		return new UnionType([
			'name' => 'Position',
			'type' => [
				Type::string(),
				self::Coordinates(),
			],
			'resolveType' => function ($value) {
				return $value->name === 'Coordinates' ? self::Coordinates() : Type::string();
			},
		]);
	}

	public static function Effects ()
	{
		return new ObjectType([
			'name' => 'Effects',
			'description' => 'Effects to perform on the image',
			'fields' => [
				'position' => [
					'name' => 'position',
					'type' => self::Position(),
				]
			],
		]);
	}

}
