<?php

namespace Developion\Toolbox;

use Craft;
use craft\console\Application as CraftConsoleApp;
use craft\web\Application as CraftWebApp;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use yii\base\Application as YiiApp;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\Module;

class Toolbox extends Module implements BootstrapInterface
{
	public const ID = 'toolbox';

	public function __construct($id = self::ID, $parent = null, $config = [])
	{
		parent::__construct($id, $parent, $config);
	}

	/**
	 * @param YiiApp $app
	 * @return void
	 */
	public function bootstrap($app)
	{
		static::setInstance($this);

		if (!($app instanceof CraftWebApp || $app instanceof CraftConsoleApp)) {
			return;
		}

		$this->registerEventHandlers();
	}

	public function registerEventHandlers()
	{

		Event::on(
			CraftWebApp::class,
			CraftWebApp::EVENT_INIT,
			static function (): void {
				if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
					/** @var HtmlDumper $dumper */
					$dumper = Craft::$app->getDumper();
					$dumper->setTheme('dark');
				}
			}
		);
	}
}
