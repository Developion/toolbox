<?php
declare(strict_types=1);

namespace Developion\Toolbox\Web\Twig;

use Craft;
use craft\helpers\StringHelper;
use Developion\Toolbox\Helpers\Template;
use Developion\Toolbox\Services\Bundles;
use Symfony\Component\VarDumper\VarDumper;
use Twig\{
	TwigFilter,
	TwigFunction,
};
use Twig\Extension\AbstractExtension;

class ToolboxExtension extends AbstractExtension
{
	public function __construct(
		private readonly ?Bundles $bundles = null,
	)
	{
	}

	public function getFunctions(): array
	{
		$functions = [
			new TwigFunction('_call', Template::staticMethodCall(...)),
			new TwigFunction('_constant', Template::staticConstantCall(...)),
			new TwigFunction('dd', static function (mixed ...$vars): never {
				foreach ($vars as $v) {
					VarDumper::dump($v);
				}

				exit(1);
			}),
		];

		if ($this->bundles !== null) {
			$functions[] = new TwigFunction('registerAsset', $this->bundles->registerAssetFile(...));
			$functions[] = new TwigFunction('getMediaFileUrl', $this->bundles->getMediaFileUrl(...));
		}

		return $functions;
	}

	public function getFilters(): array
	{
		return [
			new TwigFilter('slugify', static function (string $str): string {
				return StringHelper::slugify($str, language: Craft::$app->getSites()->getCurrentSite()->language);
			}),
		];
	}
}
