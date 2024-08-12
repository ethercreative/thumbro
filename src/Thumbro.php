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
use craft\events\RegisterGqlDirectivesEvent;
use craft\events\RegisterGqlTypesEvent;
use craft\services\Gql;
use craft\web\twig\variables\CraftVariable;
use ether\thumbro\gql\ThumbroTypes;
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

		Event::on(
			Gql::class,
			Gql::EVENT_REGISTER_GQL_TYPES,
			[$this, 'onRegisterGqlTypes']
		);

		Event::on(
			Gql::class,
			Gql::EVENT_REGISTER_GQL_DIRECTIVES,
			[$this, 'onRegisterGqlDirectives']
		);
	}

	// Settings
	// =========================================================================

	protected function createSettingsModel (): ?Model
	{
		return new Settings();
	}

	/**
	 * @return bool|Model|Settings
	 */
	public function getSettings (): ?Model
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

	public function onRegisterGqlTypes (RegisterGqlTypesEvent $event)
	{
		ThumbroTypes::register($event);
	}

	public function onRegisterGqlDirectives (RegisterGqlDirectivesEvent $event)
	{
		$event->directives[] = ThumbroDirective::class;
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
