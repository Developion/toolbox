<?php
declare(strict_types=1);

namespace Developion\Toolbox\Services;

use Craft;
use craft\fields\Dropdown;
use craft\web\View;
use Developion\IUFRO\Web\Assets\Front\FrontAsset;
use Developion\Toolbox\Events\BundlesServiceConfigEvent;
use Developion\Toolbox\Helpers\{
	Colors,
	CraftHelper,
};
use Developion\Toolbox\Models\Color;
use Exception;
use Illuminate\Support\Arr;
use Throwable;
use yii\base\{
	Component,
	Event,
};

class Bundles extends Component
{
	/** @var array<string, bool> $cached */
	private array $cached = [];
	public array $css = [];
	public array $js = [];
	public array $colors = [];
	protected string $basePathAlias = '';
	protected string $baseUrlAlias = '';
	protected mixed $colorNamespace;

	// Default Config
	public const DEFAULT_COLORS = [];
	public const DEFAULT_COLOR_NAMESPACE = 'toolbox';
	public const DEFAULT_RELATIVE_PATH = 'cached-assets';

	// Events
	public const EVENT_BUNDLES_SERVICE_CONFIG = 'eventBundlesServiceConfig';

	public function init(): void
	{
		$this->configure();
		Craft::$app->getView()->on(View::EVENT_BEGIN_PAGE, $this->registerStyle(...));
		Craft::$app->getView()->on(View::EVENT_END_PAGE, $this->registerStyle(...));
	}

	protected function configure(): void
	{
		$config = [
			'colors' => self::DEFAULT_COLORS,
			'colorNamespace' => self::DEFAULT_COLOR_NAMESPACE,
			'relativePath' => self::DEFAULT_RELATIVE_PATH,
		];
		$configPath = Craft::$app->getPath()->getConfigPath() . '/developion-toolbox.php';
		try {
			$configFile = require $configPath;
			$config = array_merge($config, $configFile);
		} catch (Throwable) {
		}
		$event = new BundlesServiceConfigEvent($config);
		Event::trigger(self::class, self::EVENT_BUNDLES_SERVICE_CONFIG, $event);
		$this->colors = $event->colors;
		$this->colorNamespace = $event->colorNamespace;
		$this->basePathAlias = "@webroot/assets/{$event->relativePath}/";
		$this->baseUrlAlias = "/assets/{$event->relativePath}/";

		$this->ensurePath();
	}

	/**
	 * @param string $bundleClass
	 * @param string|string[] $assetPath
	 */
	public function registerAssetFile(string $bundleClass, array|string $assetPath, array $options = []): void
	{
		if (empty($assetPath)) return;

		$assetPaths = Arr::wrap($assetPath);
		foreach ($assetPaths as $assetPath) {
			preg_replace_callback('/\.(js|css)$/', function ($matches) use ($bundleClass, $assetPath, $options) {
				$extension = substr($matches[0], 1);
				if (array_key_exists(json_encode($options), $this->$extension) && in_array($assetPath, $this->$extension[json_encode($options)])) return;
				$assetManager = Craft::$app->getAssetManager();
				$bundle = $assetManager->getBundle($bundleClass);
				$path = $assetManager->getAssetPath($bundle, $assetPath);
				$this->$extension[json_encode($options)][$assetPath] = match ($extension) {
					'css' => file_get_contents($path),
					'js' => ';' . file_get_contents($path),
					default => throw new Exception('Provided path is not a js or css file.'),
				};
			}, $assetPath);
		}
	}

	private function registerStyle(Event $event): void {
		collect(['css', 'js'])->each(function (string $extension) use ($event): void {
			$cacheKey = md5(Craft::$app->getRequest()->getFullUri() . Craft::$app->getRequest()->getQueryStringWithoutPath() . $extension);

			$assets = [];
			if ($event->name === View::EVENT_BEGIN_PAGE) {
				if (is_array($assets = Craft::$app->getCache()->get($cacheKey))) {
					$this->cached[$cacheKey] = true;
				}
			}

			if ($event->name === View::EVENT_END_PAGE && !array_key_exists($cacheKey, $this->cached)) {
				$assets = $this->$extension;
				Craft::$app->getCache()->set(
					$cacheKey,
					$assets,
				);
			}
			if (!is_array($assets)) {
				$assets = [];
			}

			$this->writeOutput($extension, $assets);
		});
	}

	public function writeOutput(string $extension, array $assets = []): void
	{
		foreach ($assets as $optionsString => $asset) {
			$options = json_decode($optionsString, true);
			$options['appendTimestamp'] = true;
			$filename = sprintf(
				'%s.%s',
				md5(Craft::$app->getRequest()->getFullUri() . Craft::$app->getRequest()->getQueryStringWithoutPath() . $optionsString),
				$extension,
			);
			$url = Craft::$app->getCache()->getOrSet(
				$filename,
				function () use ($filename, $asset): string {
					$path = Craft::getAlias($this->basePathAlias . $filename);
					$url = $this->baseUrlAlias . $filename;
					file_put_contents($path, implode('', $asset));
					return $url;
				}
			);

			match ($extension) {
				'css' => Craft::$app->getView()->registerCssFile($url, $options),
				'js' => Craft::$app->getView()->registerJsFile($url, $options),
				default => throw new Exception('Provided path is not a js or css file.'),
			};
		}
	}

	private function ensurePath(): void
	{
		if (!is_dir(Craft::getAlias($this->basePathAlias))) {
			mkdir(Craft::getAlias($this->basePathAlias), 0755, true);
		}
	}

	public function getMediaFileUrl(string $bundleClass, string $assetPath): string
	{
		$assetManager = Craft::$app->getAssetManager();
		$url = $assetManager->getPublishedUrl(
			$assetManager->getBundle($bundleClass)->sourcePath,
			false,
			$assetPath,
		);
		return $url;
	}

	public function registerDashboardColors(Dropdown $colorField): string
	{
		$css = $this->dashboardColorsWrapperStyle('bg-');
		foreach ($colorField->options as $colorOption) {
			if (!$colorStyle = $this->dashboardColorsItemStyle($colorOption)) continue;
			$css .= $colorStyle;
		}
		return $css;
	}

	private function dashboardColorsWrapperStyle(string $prefix): string
	{
		$styleFormat = '.selectize-dropdown-content .option{cursor:pointer!important}.selectize-input .item[data-value*="base64:%1$s"]{position:relative;width:100%%}.selectize-input .item[data-value*="base64:%1$s"]>div{visibility:hidden;width:0}.selectize-input .item[data-value*="base64:%1$s"]:after{content:"";height:20px;position:absolute;left:0;top:0;width:100%%}';
		return sprintf($styleFormat, base64_encode($prefix));
	}

	private function dashboardColorsItemStyle(array $aColor): false|string
	{
		if (empty($aColor['value'])) {
			return '';
		}
		if (!array_key_exists($aColor['value'], $this->colors)) {
			return false;
		}

		$color = new Color(stripos($aColor['value'], $this->colorNamespace) !== false && array_key_exists($aColor['value'], $this->colors) ? $this->colors[$aColor['value']] : Colors::nameToHex($aColor['label']));
		$styleFormat = '.selectize-input .item[data-value="base64:%1$s"]:after{background-color:#%2$s}.selectize-dropdown-content .option[data-value="base64:%1$s"]{background-color:#%2$s;color:#%3$s}.selectize-dropdown-content .option[data-value="base64:%1$s"]:hover{color:#%2$s;background-color:#%3$s}';

		return sprintf(
			$styleFormat,
			base64_encode($aColor['value']),
			$color->getHex(),
			$color->isDark() ? $color->lighten(100) : $color->darken(100),
		) . PHP_EOL;
	}
}
