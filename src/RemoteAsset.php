<?php
/**
 * Thumbro for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2020 Ether Creative
 */

namespace ether\thumbro;

/**
 * Class RemoteAsset
 *
 * @author  Ether Creative
 * @package ether\thumbro
 */
class RemoteAsset
{

	public $url;
	public $width;
	public $height;
	public $extension;

	public function __construct ($url)
	{
		$this->url = $url;
		$size = getimagesize($url);
		$this->width = $size[0];
		$this->height = $size[1];
		$this->extension = [
			0  => 'UNKNOWN',
			1  => 'gif',
			2  => 'jpeg',
			3  => 'png',
			4  => 'swf',
			5  => 'psd',
			6  => 'bmp',
			7  => 'tiff',
			8  => 'tiff',
			9  => 'jpc',
			10 => 'jp2',
			11 => 'jpx',
			12 => 'jb2',
			13 => 'swc',
			14 => 'iff',
			15 => 'wbmp',
			16 => 'xbm',
			17 => 'ico',
			18 => 'count',
		][$size[2]];
	}

	public function getUrl ()
	{
		return $this->url;
	}

	public function getWidth ()
	{
		return $this->width;
	}

	public function getHeight ()
	{
		return $this->height;
	}

	public function getExtension ()
	{
		return $this->extension;
	}

	public function getFocalPoint ()
	{
		return null;
	}

}
