<?php

namespace LilleBitte\Annotations\Tests;

use LilleBitte\Annotations\AnnotationException;
use LilleBitte\Annotations\Exception\ClassNotExistsException;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use RuntimeException;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class AnnotationExceptionTest extends TestCase
{
	/**
	 * @expectedException LilleBitte\Annotations\Exception\SyntaxErrorException
	 */
	public function testCanThrowSyntaxErrorException()
	{
		throw AnnotationException::syntaxError("dummyMethod", '@');
	}

	/**
	 * @expectedException LilleBitte\Annotations\Exception\ClassNotExistsException
	 */
	public function testCanThrowClassNotExistsException()
	{
		throw AnnotationException::classNotExists("dummyMethod", '@');
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testCanThrowRuntimeException()
	{
		throw AnnotationException::runtime('dummyMethod', 'test.');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCanThrowInvalidArgumentException()
	{
		throw AnnotationException::invalidArgument('dummyMethod', 'test.');
	}
}
