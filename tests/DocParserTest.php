<?php

namespace LilleBitte\Annotations\Tests;

use SplPriorityQueue;
use Foo\Bar\Baz;
use LilleBitte\Annotations\ClassRegistry;
use LilleBitte\Annotations\DocLexer;
use LilleBitte\Annotations\DocParser;
use LilleBitte\Annotations\Exception\ClassNotExistsException;
use LilleBitte\Annotations\Tests\Fixtures\Foo as FooFixtures;
use PHPUnit\Framework\TestCase;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class DocParserTest extends TestCase
{
    public function testCanGetInstanceWithNonNullLexer()
    {
        $lexer = new DocLexer();
        $parser = new DocParser($lexer);
        $this->assertInstanceOf(DocParser::class, $parser);
    }

    public function testCanResolveClassAlias()
    {
        ClassRegistry::register(FooFixtures::class);

        $annotation = "/**\n * @FooFixtures\n */";
        $parser = new DocParser();
        $parser->setClassUses(
            \LilleBitte\Annotations\getClassUses(__FILE__)
        );
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));

        ClassRegistry::reset();

        $this->assertInstanceOf(FooFixtures::class, $list[0]->instance);
    }

    public function testCanResolveClassWithoutNamespace()
    {
        ClassRegistry::register(SplPriorityQueue::class);

        $annotation = "/**\n * @SplPriorityQueue\n */";
        $parser = new DocParser();
        $parser->setClassUses(
            \LilleBitte\Annotations\getClassUses(__FILE__)
        );
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));

        ClassRegistry::reset();

        $this->assertInstanceOf(\SplPriorityQueue::class, $list[0]->instance);
    }

    /**
     * @expectedException LilleBitte\Annotations\Exception\ClassNotExistsException
     */
    public function testCanThrowExceptionIfClassValueAnnotationNotExist()
    {
        ClassRegistry::register("Foo\\Bar\\Baz");

        try {
            $annotation = "/**\n * @Foo\\Bar\\Baz\n */";
            $parser = new DocParser();
            $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));
        } catch (\LilleBitte\Annotations\Exception\ClassNotExistsException $ce) {
            ClassRegistry::reset();
            throw $ce;
        }
    }

    public function testCanGetResolveClassWithEmptyParameters()
    {
        ClassRegistry::register(FooFixtures::class);

        $annotation = "/**\n * @FooFixtures()\n */";
        $parser = new DocParser();
        $parser->setClassUses(
            \LilleBitte\Annotations\getClassUses(__FILE__)
        );
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));

        ClassRegistry::reset();

        $this->assertInstanceOf(FooFixtures::class, $list[0]->instance);
    }

    public function testCanGetClassUses()
    {
        $parser = new DocParser();
        $parser->setClassUses(
            \LilleBitte\Annotations\getClassUses(__FILE__)
        );
        $this->assertNotEmpty($parser->getClassUses());
    }

    public function testCanParseClassValueConstructorArguments()
    {
        ClassRegistry::register(FooFixtures::class);

        $annotation = "/**\n * @FooFixtures(10, -1, \"a\", +1, -1.1, +1.1, a=10, true, " .
            "false, {\"x\", \"y\"},)\n */";
        $parser = new DocParser();
        $parser->setClassUses(
            \LilleBitte\Annotations\getClassUses(__FILE__)
        );
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));

        ClassRegistry::reset();

        $this->assertInstanceOf(FooFixtures::class, $list[0]->instance);
    }

    /**
     * @expectedException \LilleBitte\Annotations\Exception\SyntaxErrorException
     */
    public function testCanThrowExceptionWhileParseClassValueConstructorArguments()
    {
        ClassRegistry::register(FooFixtures::class);

        try {
            $annotation = "/**\n * @FooFixtures(x, y)\n */";
            $parser = new DocParser();
            $parser->setClassUses(
                \LilleBitte\Annotations\getClassUses(__FILE__)
            );
            $parser->parse($annotation, sprintf("class %s", get_class($this)));
        } catch (\LilleBitte\Annotations\Exception\SyntaxErrorException $se) {
            ClassRegistry::reset();
            throw $se;
        }
    }

    public function testCanParseEmptyArray()
    {
        ClassRegistry::register(FooFixtures::class);

        $annotation = "/**\n * @FooFixtures({})\n */";
        $parser = new DocParser();
        $parser->setClassUses(
            \LilleBitte\Annotations\getClassUses(__FILE__)
        );
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));

        ClassRegistry::reset();

        $this->assertInstanceOf(FooFixtures::class, $list[0]->instance);
    }

    public function testCanParseArrayWithTrailingComma()
    {
        ClassRegistry::register(FooFixtures::class);

        $annotation = "/**\n * @FooFixtures({10,})\n */";
        $parser = new DocParser();
        $parser->setClassUses(
            \LilleBitte\Annotations\getClassUses(__FILE__)
        );
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));

        ClassRegistry::reset();

        $this->assertInstanceOf(FooFixtures::class, $list[0]->instance);
    }

    /**
     * @expectedException \LilleBitte\Annotations\Exception\SyntaxErrorException
     */
    public function testCanThrowExceptionWhileTokenAssertionHasFailed()
    {
        ClassRegistry::register(FooFixtures::class);

        try {
            $annotation = "/**\n * @FooFixtures(10\n */";
            $parser = new DocParser();
            $parser->setClassUses(
                \LilleBitte\Annotations\getClassUses(__FILE__)
            );
            $parser->parse($annotation, sprintf("class %s", get_class($this)));
        } catch (\LilleBitte\Annotations\Exception\SyntaxErrorException $se) {
            ClassRegistry::reset();
            throw $se;
        }
    }
}
