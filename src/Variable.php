<?php
/**
 * Thumbro for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2020 Ether Creative
 */

namespace ether\thumbro;

use Craft;
use craft\elements\Asset;
use craft\helpers\ArrayHelper;
use Exception;
use Twig\Markup;
use yii\web\View;
use function Arrayy\array_first;

/**
 * Class Variable
 *
 * @author  Ether Creative
 * @package ether\thumbro
 */
class Variable
{

	/**
	 * @param Asset|string $asset
	 * @param array $transforms
	 * @param array $config
	 *
	 * @return ThumbroImage[]
	 */
	public function img ($asset, array $transforms, array $config = [])
	{
		if (is_string($asset))
			$asset = new RemoteAsset($asset);

		$thumbro = Thumbro::getInstance();
		$settings = $thumbro->getSettings();
		$single = false;

		if (ArrayHelper::isAssociative($transforms, true))
		{
			$single = true;
			$transforms = [$transforms];
		}

		if ($settings->autoFocalPoint && !array_key_exists('position', $config))
			$config['position'] = $asset->getFocalPoint();

		if ($settings->autoCompress && !array_key_exists('auto', $config))
			$config['auto'] = 'format,compress';

		foreach ($transforms as $i => $transform)
			$transforms[$i] = array_merge($transform, $config);

		$transformed = $thumbro->service->transform($asset, $transforms);
		return $single ? array_first($transformed) : $transformed;
	}

	/**
	 * @param Asset|string $asset
	 * @param array $transform
	 * @param array $config
	 *
	 * @return Markup
	 * @throws Exception
	 */
	public function picture ($asset, array $transform, array $config = [])
	{
		if (is_string($asset))
			$asset = new RemoteAsset($asset);

		if (!ArrayHelper::isAssociative($transform, true))
			throw new Exception('The `picture` method only supports a single transform!');

		if (!array_key_exists('width', $transform) && !array_key_exists('height', $transform))
			throw new Exception('You must specify width and height in the transform!');

		$aboveFold = false;
		if (array_key_exists('aboveFold', $config))
		{
			$aboveFold = $config['aboveFold'];
			unset($config['aboveFold']);
		}

		$noJS = false;
		if (array_key_exists('noJs', $config))
		{
			$noJS = (bool) $config['noJs'];
			unset($config['noJs']);
		}

		$w = $transform['width'];
		$h = $transform['height'];

		$transforms = [
			array_merge($transform, [ 'width' => 20, 'height' => round($h * 20 / $w) ]), // Placeholder
			$transform, // Base size
			array_merge($transform, [ 'width' => round($w * 1.5), 'height' => round($h * 1.5) ]), // 1.5x
			array_merge($transform, [ 'width' => round($w * 2), 'height' => round($h * 2) ]), // 2x
		];

		$imgs = $this->img($asset, $transforms, $config);

		$placeholder = file_get_contents($this->_getThumborUrl($imgs[0]->url));
		$placeholder = 'data:' . $asset->mimeType . ';base64,' . base64_encode($placeholder);

		$padding = $h / $w * 100;

		if ($aboveFold) {
			$markup = <<<HTML
<picture>
	<img
		width="$w"
		height="$h"
		src="{$placeholder}"
		alt="$asset->title"
	/>
	<div style="padding-top:{$padding}%"></div>
	<img
		loading="eager"
		width="$w"
		height="$h"
		src="{$imgs[1]->url}"
		srcset="{$imgs[1]->url} 1x, {$imgs[2]->url} 2x, {$imgs[3]->url} 3x"
		alt="$asset->title"
	/>
</picture>
HTML;
		} else {
			if (!$noJS)
			{
				Craft::$app->getView()->registerJs(
					file_get_contents(__DIR__ . '/assets/dynamic.js'),
					View::POS_END
				);
			}

			$markup = <<<HTML
<picture>
	<img
		width="$w"
		height="$h"
		src="{$placeholder}"
		alt="$asset->title"
	/>
	<div style="padding-top:{$padding}%"></div>
	<noscript>
		<img
			loading="lazy"
			width="$w"
			height="$h"
			src="{$imgs[1]->url}"
			srcset="{$imgs[1]->url} 1x, {$imgs[2]->url} 2x, {$imgs[3]->url} 3x"
			alt="$asset->title"
		/>
	</noscript>
</picture>
HTML;
		}

		return new Markup($markup, 'utf8');
	}

	// Helpers
	// =========================================================================

	private function _getThumborUrl ($url)
	{
		$settings = Thumbro::getInstance()->getSettings();

		if (is_callable($settings->thumborUrlModifier))
			$url = call_user_func($settings->thumborUrlModifier, $url);

		return $url;
	}

}
