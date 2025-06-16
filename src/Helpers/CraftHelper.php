<?php
declare(strict_types=1);

namespace Developion\Toolbox\Helpers;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;

class CraftHelper
{
	public const PHP_SUFFIX = '.php';

	protected static ?int $version = null;

	protected static function getVersion(): int
	{
		if (self::$version === null) {
			$versionString = Craft::$app->getVersion();
			$parts = explode('.', $versionString);
			self::$version = (int)$parts[0];
		}

		return self::$version;
	}

	public static function devMode(): bool
	{
		if (defined('YII_DEBUG')) {
			return YII_DEBUG;
		}

		return true;
	}

	/**
	 * @param string $fileName
	 *
	 * @return array
	 */
	public static function getConfigFromFile(string $fileName): array
	{
		$fileName .= self::PHP_SUFFIX;
		$currentEnv = Craft::$app->getConfig()->env;

		$path = Craft::getAlias('@config/' . $fileName, false);
		if ($path === false || !file_exists($path)) {
			$path = Craft::getAlias('@developion/toolbox/config.php', false);
			if ($path === false || !file_exists($path)) {
				return [];
			}
		}
		// Make sure we got a config file
		if (!is_array($config = @include $path)) {
			return [];
		}
		// If it's not a multi-environment config, return the whole thing
		if (!array_key_exists('*', $config)) {
			return $config;
		}
		// If no environment was specified, just look in the '*' array
		if ($currentEnv === null) {
			return $config['*'];
		}
		$mergedConfig = [];
		foreach ($config as $env => $envConfig) {
			if ($env === '*' || StringHelper::contains($currentEnv, $env)) {
				$mergedConfig = ArrayHelper::merge($mergedConfig, $envConfig);
			}
		}

		return $mergedConfig;
	}
}
