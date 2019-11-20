<?php

namespace LilleBitte\Annotations\Tests;

use LilleBitte\Annotations\AnnotationReader;
use LilleBitte\Annotations\ClassRegistry;
use LilleBitte\Annotations\DocParser;
use LilleBitte\Annotations\ReaderInterface;
use LilleBitte\Annotations\Tests\Fixtures\Foo;
use LilleBitte\Annotations\Tests\Fixtures\Bar;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 * @Foo
 */
class AnnotationReaderTest extends TestCase
{
	/**
	 * @var mixed
	 * @Foo
	 */
	private $foo;

	/**
	 * @Foo
	 */
	private function dummyMethodForTesting()
	{
	}

	public function tearDown()
	{
		$this->docComments = null;
	}

	public function testCanGetInstanceWithPrebuiltParser()
	{
		$reader = new AnnotationReader();
		$this->assertInstanceOf(ReaderInterface::class, $reader);
	}

	public function testCanGetInstanceWithNonNullParser()
	{
		$parser = new DocParser();
		$this->assertInstanceOf(DocParser::class, $parser);
		$reader = new AnnotationReader($parser);
		$this->assertInstanceOf(ReaderInterface::class, $reader);
	}

	public function testCanGetListOfInstanceFromAnnotatedClass()
	{
		ClassRegistry::register(Foo::class);

		$reader = new AnnotationReader();
		$foo = $reader->getClassAnnotations(new ReflectionClass($this));

		ClassRegistry::reset();

		$this->assertInstanceOf(Foo::class, $foo[0]->instance);
	}

	public function testCanGetSpecificClassInstanceFromAnnotatedClass()
	{
		ClassRegistry::register(Foo::class);

		$reader = new AnnotationReader();
		$foo = $reader->getClassAnnotation(
			new ReflectionClass($this),
			Foo::class
		);

		ClassRegistry::reset();

		$this->assertInstanceOf(Foo::class, $foo);
	}

	public function testCannotGetSpecificClassInstanceFromAnnotatedClass()
	{
		ClassRegistry::register(Foo::class);

		$reader = new AnnotationReader();
		$foo = $reader->getClassAnnotation(
			new ReflectionClass($this),
			Bar::class
		);

		ClassRegistry::reset();

		$this->assertNull($foo);
	}

	public function testCanGetListOfInstanceFromAnnotatedMethod()
	{
		ClassRegistry::register(Foo::class);

		$reader = new AnnotationReader();
		$refl = (new ReflectionClass($this))->getMethod('dummyMethodForTesting');
		$foo = $reader->getMethodAnnotations($refl);

		ClassRegistry::reset();

		$this->assertInstanceOf(Foo::class, $foo[0]->instance);
	}

	public function testCanGetSpecificClassInstanceFromAnnotatedMethod()
	{
		ClassRegistry::register(Foo::class);

		$reader = new AnnotationReader();
		$refl = (new ReflectionClass($this))->getMethod('dummyMethodForTesting');
		$foo = $reader->getMethodAnnotation($refl, Foo::class);

		ClassRegistry::reset();

		$this->assertInstanceOf(Foo::class, $foo);
	}

	public function testCannotGetSpecificClassInstanceFromAnnotatedMethod()
	{
		ClassRegistry::register(Foo::class);

		$reader = new AnnotationReader();
		$refl = (new ReflectionClass($this))->getMethod('dummyMethodForTesting');
		$foo = $reader->getMethodAnnotation($refl, Bar::class);

		ClassRegistry::reset();

		$this->assertNull($foo);
	}

	public function testCanGetListOfInstanceFromAnnotatedProperty()
	{
		ClassRegistry::register(Foo::class);

		$reader = new AnnotationReader();
		$refl = (new ReflectionClass($this))->getProperty('foo');
		$foo = $reader->getPropertyAnnotations($refl);

		ClassRegistry::reset();

		$this->assertInstanceOf(Foo::class, $foo[0]->instance);
	}

	public function testCanGetSpecificClassInstanceFromAnnotatedProperty()
	{
		ClassRegistry::register(Foo::class);

		$reader = new AnnotationReader();
		$refl = (new ReflectionClass($this))->getProperty('foo');
		$foo = $reader->getPropertyAnnotation($refl, Foo::class);

		ClassRegistry::reset();

		$this->assertInstanceOf(Foo::class, $foo);
	}

	public function testCannotGetSpecificClassInstanceFromAnnotatedProperty()
	{
		ClassRegistry::register(Foo::class);

		$reader = new AnnotationReader();
		$refl = (new ReflectionClass($this))->getProperty('foo');
		$foo = $reader->getPropertyAnnotation($refl, Bar::class);

		ClassRegistry::reset();

		$this->assertNull($foo);
	}
}
