<?php

declare(strict_types=1);

namespace LilleBitte\Annotations;

use function array_slice;
use function count;
use function explode;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
final class ClassRegistry
{
	/**
	 * @var array
	 */
	private static $classes = [];

	/**
	 * Register class name.
	 *
	 * @param string $class Class name.
	 * @return void
	 */
	public static function register(string $class)
	{
		$className = self::getClassName($class);
		$namespace = self::getNamespace($class);

		self::$classes[$className] = [
			'class' => $className,
			'namespace' => $namespace
		];
	}

	/**
	 * Check if given class name is exist in
	 * registry container.
	 *
	 * @param string $class Class name.
	 * @return boolean
	 */
	public static function has(string $class)
	{
		$className = self::getClassName($class);
		return isset(self::$classes[$className]);
	}

	/**
	 * Get pair of [class, namespace] from registry
	 * container.
	 *
	 * @param string $class Class name.
	 * @return array|null
	 */
	public static function get(string $class)
	{
		if (!self::has($class)) {
			return null;
		}

		$className = self::getClassName($class);
		return self::$classes[$className];
	}

	/**
	 * Reset registry container.
	 *
	 * @return void
	 */
	public static function reset()
	{
		self::$classes = [];
	}

	/**
	 * Get only class name portion from
	 * given class name.
	 *
	 * @param string $class Class name.
	 * @return string
	 */
	private static function getClassName(string $class)
	{
		$splitted = explode("\\", $class);
		$className = count($splitted) === 1
			? $class
			: $splitted[count($splitted) - 1];

		return $className;
	}

	/**
	 * Get only namespace name portion from
	 * given class name.
	 *
	 * @param string $class Class name.
	 * @return string
	 */
	private static function getNamespace(string $class)
	{
		$splitted = explode("\\", $class);
		$namespace = count($splitted) === 1
			? ''
			: join("\\", array_slice($splitted, 0, count($splitted) - 1));

		return $namespace;
	}
}
