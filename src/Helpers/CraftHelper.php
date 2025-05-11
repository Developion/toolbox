<?php
declare(strict_types=1);

namespace Developion\Toolbox\Helpers;

use Craft;
use craft\web\Application;

class CraftHelper
{
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
}
