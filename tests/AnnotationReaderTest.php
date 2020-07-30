<?php

namespace LilleBitte\Annotations\Tests;

use LilleBitte\Annotations\AnnotationReader;
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

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
    }

    public function testCanGetInstanceWithPrebuiltParser()
    {
        $reader = new AnnotationReader();
        $this->assertInstanceOf(ReaderInterface::class, $reader);
    }

    public function testCanGetInstanceWithNonNullParser()
    {
        $reader = new AnnotationReader();
        $this->assertInstanceOf(ReaderInterface::class, $reader);
    }

    public function testCanGetListOfInstanceFromAnnotatedClass()
    {
        $reader = new AnnotationReader();
        $reader->setIgnoredAnnotationNames(['gmail']);
        $foo = $reader->getClassAnnotations(new ReflectionClass($this));
        $this->assertInstanceOf(Foo::class, $foo[0]->instance);
    }

    public function testCanGetSpecificClassInstanceFromAnnotatedClass()
    {
        $reader = new AnnotationReader();
        $reader->setIgnoredAnnotationNames(['gmail']);
        $foo = $reader->getClassAnnotation(new ReflectionClass($this), Foo::class);
        $this->assertInstanceOf(Foo::class, $foo);
    }

    public function testCannotGetSpecificClassInstanceFromAnnotatedClass()
    {
        $reader = new AnnotationReader();
        $reader->setIgnoredAnnotationNames(['gmail']);
        $foo = $reader->getClassAnnotation(new ReflectionClass($this), Bar::class);
        $this->assertNull($foo);
    }

    public function testCanGetListOfInstanceFromAnnotatedMethod()
    {
        $reader = new AnnotationReader();
        $reader->setIgnoredAnnotationNames(['gmail']);
        $refl = (new ReflectionClass($this))->getMethod('dummyMethodForTesting');
        $foo = $reader->getMethodAnnotations($refl);
        $this->assertInstanceOf(Foo::class, $foo[0]->instance);
    }

    public function testCanGetSpecificClassInstanceFromAnnotatedMethod()
    {
        $reader = new AnnotationReader();
        $reader->setIgnoredAnnotationNames(['gmail']);
        $refl = (new ReflectionClass($this))->getMethod('dummyMethodForTesting');
        $foo = $reader->getMethodAnnotation($refl, Foo::class);
        $this->assertInstanceOf(Foo::class, $foo);
    }

    public function testCannotGetSpecificClassInstanceFromAnnotatedMethod()
    {
        $reader = new AnnotationReader();
        $reader->setIgnoredAnnotationNames(['gmail']);
        $refl = (new ReflectionClass($this))->getMethod('dummyMethodForTesting');
        $foo = $reader->getMethodAnnotation($refl, Bar::class);
        $this->assertNull($foo);
    }

    public function testCanGetListOfInstanceFromAnnotatedProperty()
    {
        $reader = new AnnotationReader();
        $reader->setIgnoredAnnotationNames(['gmail']);
        $refl = (new ReflectionClass($this))->getProperty('foo');
        $foo = $reader->getPropertyAnnotations($refl);
        $this->assertInstanceOf(Foo::class, $foo[0]->instance);
    }

    public function testCanGetSpecificClassInstanceFromAnnotatedProperty()
    {
        $reader = new AnnotationReader();
        $reader->setIgnoredAnnotationNames(['gmail']);
        $refl = (new ReflectionClass($this))->getProperty('foo');
        $foo = $reader->getPropertyAnnotation($refl, Foo::class);
        $this->assertInstanceOf(Foo::class, $foo);
    }

    public function testCannotGetSpecificClassInstanceFromAnnotatedProperty()
    {
        $reader = new AnnotationReader();
        $reader->setIgnoredAnnotationNames(['gmail']);
        $refl = (new ReflectionClass($this))->getProperty('foo');
        $foo = $reader->getPropertyAnnotation($refl, Bar::class);
        $this->assertNull($foo);
    }
}
