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
 * Class Settings
 *
 * @author  Ether Creative
 * @package ether\thumbro
 */
class Settings extends Model
{

	// Properties
	// =========================================================================

	/** @var string */
	public $domain;

	/** @var string|null */
	public $securityKey;

	/** @var bool */
	public $autoFocalPoint = true;

	/** @var bool */
	public $autoCompress = true;

	/** @var callable|null */
	public $imageUrlModifier = null;

	/** @var callable|null */
	public $thumborUrlModifier = null;

}
