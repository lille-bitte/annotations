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
