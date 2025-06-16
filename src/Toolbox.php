<?php

namespace Developion\Toolbox;

use Craft;
use craft\console\Application as CraftConsoleApp;
use craft\web\Application as CraftWebApp;
use Developion\Toolbox\Helpers\CraftHelper;
use Developion\Toolbox\Models\Settings;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use yii\base\Application as YiiApp;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\Module;

class Toolbox extends Module implements BootstrapInterface
{
	public const ID = 'toolbox';

	public static ?Settings $settings = null;

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

		$this->configureModule();

		$this->registerEventHandlers();
	}

	protected function configureModule(): void
	{
		Craft::setAlias('@developion/toolbox', $this->getBasePath());

		$config = CraftHelper::getConfigFromFile($this->id);
		self::$settings = new Settings($config);
	}

	public function registerEventHandlers(): void
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
