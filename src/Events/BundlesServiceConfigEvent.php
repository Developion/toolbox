<?php
declare(strict_types=1);

namespace Developion\Toolbox\Events;

use yii\base\Event;

class BundlesServiceConfigEvent extends Event
{
	/**
	 * @var array<string, string>[] $colors
	 */
	public array $colors;
	public string $colorNamespace;
	public string $relativePath;
}
