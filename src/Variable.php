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
	 * @param Asset $asset
	 * @param array $transforms
	 * @param array $config
	 *
	 * @return ThumbroImage[]
	 */
	public function img (Asset $asset, array $transforms, array $config = [])
	{
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
	 * @param Asset $asset
	 * @param array $transform
	 * @param array $config
	 *
	 * @return Markup
	 * @throws Exception
	 */
	public function picture (Asset $asset, array $transform, array $config = [])
	{
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
					'!function(){for(var t=function(t){var e=t.lastElementChild;e.setAttribute("src",e.dataset.src),e.setAttribute("srcset",e.dataset.srcset),e.removeAttribute("data-src"),e.removeAttribute("data-srcset")},e=document.querySelectorAll("picture"),r=new IntersectionObserver(function(e,r){var n=!0,i=!1,o=void 0;try{for(var s,c=e[Symbol.iterator]();!(n=(s=c.next()).done);n=!0){var u=s.value;if(!(u.intersectionRatio<=0)){var a=u.target;r.unobserve(a),t(a)}}}catch(t){i=!0,o=t}finally{try{n||null==c.return||c.return()}finally{if(i)throw o}}},{rootMargin:"20px 0px",threshold:.01}),n=function(n,i){var o=e[n],s=document.createElement("div"),c=o.querySelector("noscript");if(!c)return"continue";s.innerHTML=c.textContent;var u=s.firstElementChild;u.style.opacity=0,u.setAttribute("data-src",u.getAttribute("src")),u.setAttribute("data-srcset",u.getAttribute("srcset")),u.removeAttribute("src"),u.removeAttribute("srcset"),u.addEventListener("load",function(){u.removeAttribute("style")}),o.appendChild(u),!function(t){return t.getBoundingClientRect().top<=(window.innerHeight||document.documentElement.clientHeight)}(o)?r.observe(o):setTimeout(function(){return t(o)},150)},i=0,o=e.length;i<o;i++)n(i)}();',
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
