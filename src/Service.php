<?php
/**
 * Thumbro for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2020 Ether Creative
 */

namespace ether\thumbro;

use Craft;
use craft\base\Component;
use craft\elements\Asset;

/**
 * Class Service
 *
 * @author  Ether Creative
 * @package ether\thumbro
 */
class Service extends Component
{

	// Properties
	// =========================================================================

	/** @var Settings */
	private $_settings;
	private $_restEndpoint;

	// Methods
	// =========================================================================

	public function init (): void
	{
		parent::init();

		$this->_settings = Thumbro::getInstance()->getSettings();
		$this->_restEndpoint = $this->_join([$this->_settings->domain, 'image']);
	}

	/**
	 * @param Asset|RemoteAsset $image
	 * @param array $transforms
	 *
	 * @return array|null
	 */
	public function transform ($image, $transforms)
	{
		if ($image->getExtension() === 'svg' || $image->getExtension() === 'UNKNOWN')
		{
			Craft::error('Thumbor does not support SVG or unknown image types.');
			return null;
		}

		$transformedImages = [];

		foreach ($transforms as $transform)
			$transformedImages[] = $this->_getTransformedImage(
				$image,
				$transform
			);

		return $transformedImages;
	}

	// Private
	// =========================================================================

	/**
	 * @param Asset                 $image
	 * @param array                 $transform
	 *
	 * @return ThumbroImage
	 */
	private function _getTransformedImage ($image, $transform)
	{
		/** @var Settings $settings */
		$settings = Thumbro::getInstance()->getSettings();

		$parts = [];
		$filters = [];

		// Format

		if ($format = @$transform['format'])
		{
			if ($format === 'jpg')
				$format = 'jpeg';

			$filters['format'] = $format;
		}

		// Trim

		if ($trim = @$transform['trim'])
		{
			if ($trim === true) $parts[] = 'trim';
			else $parts[] = 'trim:' . $trim;
		}

		// Mode

		if ($mode = @$transform['mode'])
		{
			switch ($mode)
			{
				default:
				case 'crop':
					// Do nothing, this is Thumbor's default
					break;
				case 'fit':
					$parts[] = 'fit-in';
					break;
				case 'stretch':
					$filters['stretch'] = null;
					break;
			}
		}

		// Size

		$size = [];
		$ratio = @$transform['ratio'];
		$width = @$transform['width'];
		$height = @$transform['height'];

		if ($width) $width = round($width);
		if ($height) $height = round($height);

		if ($width)
			$size[] = $width;
		else
			$size[] = $ratio && $height ? $height * $ratio : '';

		if ($height)
			$size[] = $height;
		else
			$size[] = $ratio && $width ? $width * $ratio : '';

		if (!empty(array_filter($size)))
			$parts[] = implode('x', $size);

		// Position

		if ($position = @$transform['position'])
		{
			if (is_array($position))
			{
				$x = $position['x'];
				$y = $position['y'];
			}
			else
			{
				list($x, $y) = explode(' ', $position);
			}

			$x = floatval($x);
			$y = floatval($y);

			if ($x > 1) $x /= 100;
			if ($y > 1) $y /= 100;

			$x = (int) ($image->getWidth() * $x);
			$y = (int) ($image->getHeight() * $y);

			if ($x < 1) $x++;
			if ($y < 1) $y++;

			$filters['focal'] = $x . 'x' . $y . ':' . ($x - 1) . 'x' . ($y - 1);
		}

		// Smart focal point detection

		if (@$transform['smart'])
			$parts[] = 'smart';

		// Upscale

		if (!@$transform['upscale'])
			$filters['no_upscale'] = '';

		// Effects

		if ($effects = @$transform['effects'])
		{
			foreach ($effects as $name => $args)
			{
				$value = $this->_parseFilterArgs($name, $args);

				if ($value === null)
					continue;

				$filters[$this->_parseFilterName($name)] = $value;
			}
		}

		// Filters

		if (!empty($filters))
		{
			$f = ['filters'];

			foreach ($filters as $name => $args)
				$f[] = $name . '(' . $args . ')';

			$parts[] = implode(':', $f);
		}

		// Saving

		$parts[] = $this->_getImageUrl($image);

		$url = [$settings->domain];

		if ($settings->securityKey)
			$url[] = $this->_generateKey($parts, $settings->securityKey);
		else
			$url[] = 'unsafe';

		$url = $this->_join(array_merge($url, $parts));

		return new ThumbroImage([
			'url' => $url,
			'width' => $size[0],
			'height' => $size[1],
		]);
	}

	// Helpers
	// =========================================================================

	private function _generateKey ($parts, $key)
	{
		$url = $this->_join($parts);
		$hash = hash_hmac('sha1', $url, $key, true);

		return strtr(
			base64_encode($hash),
			'/+', '_-'
		);
	}

	private function _join ($parts = [])
	{
		return Thumbro::join($parts);
	}

	private function _parseFilterName ($name)
	{
		$name = preg_replace('/(?<=\\w)(?=[A-Z])/','_$1', $name);
		$name = strtolower($name);

		return $name;
	}

	private function _parseFilterArgs ($name, $args)
	{
		if (in_array($name, ['equalize', 'grayscale']))
		{
			if ($args) return '';
			return null;
		}

		return $this->_parseFilterArgsValue($args);
	}

	private function _parseFilterArgsValue ($args)
	{
		if (is_bool($args))
			return $args ? 'True' : 'False';

		if (is_array($args))
			return implode(',', array_map([$this, '_parseFilterArgsValue'], $args));

		return $args;
	}

	/**
	 * @param Asset|RemoteAsset $asset
	 *
	 * @return string
	 */
	private function _getImageUrl ($asset)
	{
		$settings = Thumbro::getInstance()->getSettings();
		$url = $asset->getUrl();

		if (is_callable($settings->imageUrlModifier))
			$url = call_user_func($settings->imageUrlModifier, $url, $asset instanceof RemoteAsset);

		return rawurlencode(
			preg_replace(
				'#^https?://#',
				'',
				strtok($url, '#')
			)
		);
	}

}
