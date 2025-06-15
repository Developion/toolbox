<?php
declare(strict_types=1);

namespace Developion\Toolbox\Helpers;

use Craft;
use craft\base\ElementInterface;

class Elements
{
	public static function eagerLoad(ElementInterface $element, array $eagerLoadingMap = []): void
	{
		Craft::$app->getElements()->eagerLoadElements($element::class, [$element], $eagerLoadingMap);
	}
}