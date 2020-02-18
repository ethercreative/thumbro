<?php
/**
 * Thumbro for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2020 Ether Creative
 */

namespace ether\thumbro;

use craft\base\Model;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Class Thumbro
 *
 * @author  Ether Creative
 * @package ether\thumbro
 * @property Service $service
 */
class Thumbro extends Plugin
{

	public function init ()
	{
		parent::init();

		$this->setComponents([
			'service' => Service::class,
		]);

		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			[$this, 'onRegisterVariable']
		);
	}

	// Settings
	// =========================================================================

	protected function createSettingsModel ()
	{
		return new Settings();
	}

	/**
	 * @return bool|Model|Settings
	 */
	public function getSettings ()
	{
		return parent::getSettings();
	}

	// Events
	// =========================================================================

	/**
	 * @param Event $event
	 *
	 * @throws InvalidConfigException
	 */
	public function onRegisterVariable (Event $event)
	{
		/** @var CraftVariable $variable */
		$variable = $event->sender;
		$variable->set('thumbro', Variable::class);
	}

	// Helpers
	// =========================================================================

	public static function join ($parts)
	{
		$parts = array_map(function ($part) {
			return rtrim($part, '/');
		}, $parts);

		return implode('/', $parts);
	}

}
