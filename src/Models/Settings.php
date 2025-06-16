<?php
declare(strict_types=1);

namespace Developion\Toolbox\Models;

use craft\base\Model;

class Settings extends Model
{
	public $combineAssets = false;

	public function defineRules(): array
	{
		return [
			[['combineAssets'], 'boolean'],
		];
	}
}