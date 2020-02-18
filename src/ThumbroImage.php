<?php
/**
 * Thumbro for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2020 Ether Creative
 */

namespace ether\thumbro;

use craft\base\Model;

/**
 * Class ThumbroImage
 *
 * @author  Ether Creative
 * @package ether\thumbro
 */
class ThumbroImage extends Model
{

	// Properties
	// =========================================================================

	/** @var string */
	public $url;

	/** @var int */
	public $width, $height;

	// Methods
	// =========================================================================

	public function __toString ()
	{
		return $this->url;
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

}
