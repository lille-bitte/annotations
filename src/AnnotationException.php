<?php

namespace LilleBitte\Annotations;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class AnnotationException
{
	public static function syntaxError($method, $expected)
	{
		return new Exception\SyntaxErrorException(
			sprintf(
				"[%s] next token must be %s",
				$method,
				$expected
			)
		);
	}

	public static function classNotExists($method, $class)
	{
		return new Exception\ClassNotExistsException(
			sprintf(
				"[%s] class (%s) not exists.",
				$method,
				$class
			)
		);
	}

	public static function runtime($method, $message)
	{
		return new \RuntimeException(
			sprintf(
				"[%s] %s",
				$method,
				$message
			)
		);
	}
}
