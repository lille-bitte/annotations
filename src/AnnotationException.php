<?php

namespace LilleBitte\Annotations;

use InvalidArgumentException;
use RuntimeException;
use LilleBitte\Annotations\Exception\SyntaxErrorException;
use LilleBitte\Exception\ClassNotExistsException;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class AnnotationException
{
	/**
	 * Return a syntax error exception.
	 *
	 * @param string $method Method name which triggers the error.
	 * @param string $expected Expected value.
	 * @return SyntaxErrorException
	 */
	public static function syntaxError($method, $expected): SyntaxErrorException
	{
		return new SyntaxErrorException(
			sprintf(
				"[%s] next token must be %s",
				$method,
				$expected
			)
		);
	}

	/**
	 * Return a class not exists exception.
	 *
	 * @param string $method Method name which triggers the error.
	 * @param string $class Class name.
	 * @return ClassNotExistsException
	 */
	public static function classNotExists($method, $class): ClassNotExistsException
	{
		return new ClassNotExistsException(
			sprintf(
				"[%s] class (%s) not exists.",
				$method,
				$class
			)
		);
	}

	/**
	 * Return a runtime exception.
	 *
	 * @param string $method Method name which triggers the error.
	 * @param string $message Error message.
	 * @return RuntimeException
	 */
	public static function runtime($method, $message): RuntimeException
	{
		return new RuntimeException(
			sprintf(
				"[%s] %s",
				$method,
				$message
			)
		);
	}

	/**
	 * Return an invalid argument exception.
	 *
	 * @param string $method Method name which triggers the error.
	 * @param string $message Error message.
	 * @return InvalidArgumentException
	 */
	public static function invalidArgument($method, $message): InvalidArgumentException
	{
		return new InvalidArgumentException(
			sprintf(
				"[%s] %s",
				$method,
				$message
			)
		);
	}
}
