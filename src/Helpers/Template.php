<?php
declare(strict_types=1);

namespace Developion\Toolbox\Helpers;

class Template
{
	/**
	 * @param class-string $className
	 * @param string $methodName
	 * @param array $params
	 */
	public static function staticMethodCall(string $className, string $methodName, array ...$params): mixed
	{
		return call_user_func($className::$methodName(...), ...$params);
	}

	/**
	 * @param class-string $className
	 * @param string $constantName
	 */
	public static function staticConstantCall(string $className, string $constantName): mixed
	{
		return $className::$constantName;
	}
}