<?php

namespace LilleBitte\Annotations\Tests;

use SplPriorityQueue;
use LilleBitte\Annotations\DocLexer;
use LilleBitte\Annotations\DocParser;
use LilleBitte\Annotations\PhpFileParser;
use LilleBitte\Annotations\Exception\ClassNotExistsException;
use LilleBitte\Annotations\Tests\Fixtures\Foo as FooFixtures;
use PHPUnit\Framework\TestCase;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class DocParserTest extends TestCase
{
    /**
     * @var PhpFileParser
     */
    private $phpFileParser;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->phpFileParser = new PhpFileParser();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
    }

    public function testCanGetInstanceWithNonNullLexer()
    {
        $lexer = new DocLexer();
        $parser = new DocParser($lexer);
        $this->assertInstanceOf(DocParser::class, $parser);
    }

    public function testCanResolveClassAlias()
    {
        $annotation = "/**\n * @FooFixtures\n */";
        $parser = new DocParser();
        $parser->setClassUses($this->phpFileParser->getClassUses(__FILE__));
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));
        $this->assertInstanceOf(FooFixtures::class, $list[0]->instance);
    }

    public function testCanResolveClassWithoutNamespace()
    {
        $annotation = "/**\n * @SplPriorityQueue\n */";
        $parser = new DocParser();
        $parser->setClassUses($this->phpFileParser->getClassUses(__FILE__));
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));
        $this->assertInstanceOf(\SplPriorityQueue::class, $list[0]->instance);
    }

    public function testCanGetResolveClassWithEmptyParameters()
    {
        $annotation = "/**\n * @FooFixtures()\n */";
        $parser = new DocParser();
        $parser->setClassUses($this->phpFileParser->getClassUses(__FILE__));
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));
        $this->assertInstanceOf(FooFixtures::class, $list[0]->instance);
    }

    public function testCanGetClassUses()
    {
        $parser = new DocParser();
        $parser->setClassUses($this->phpFileParser->getClassUses(__FILE__));
        $this->assertNotEmpty($parser->getClassUses());
    }

    public function testCanParseClassValueConstructorArguments()
    {
        $annotation = "/**\n * @FooFixtures(10, -1, \"a\", +1, -1.1, +1.1, a=10, true, " .
            "false, {\"x\", \"y\"},)\n */";
        $parser = new DocParser();
        $parser->setClassUses($this->phpFileParser->getClassUses(__FILE__));
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));
        $this->assertInstanceOf(FooFixtures::class, $list[0]->instance);
    }

    /**
     * @expectedException \LilleBitte\Annotations\Exception\SyntaxErrorException
     */
    public function testCanThrowExceptionWhileParseClassValueConstructorArguments()
    {
        try {
            $annotation = "/**\n * @FooFixtures(x, y)\n */";
            $parser = new DocParser();
            $parser->setClassUses($this->phpFileParser->getClassUses(__FILE__));
            $parser->parse($annotation, sprintf("class %s", get_class($this)));
        } catch (\LilleBitte\Annotations\Exception\SyntaxErrorException $se) {
            throw $se;
        }
    }

    public function testCanParseEmptyArray()
    {
        $annotation = "/**\n * @FooFixtures({})\n */";
        $parser = new DocParser();
        $parser->setClassUses($this->phpFileParser->getClassUses(__FILE__));
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));
        $this->assertInstanceOf(FooFixtures::class, $list[0]->instance);
    }

    public function testCanParseArrayWithTrailingComma()
    {
        $annotation = "/**\n * @FooFixtures({10,})\n */";
        $parser = new DocParser();
        $parser->setClassUses($this->phpFileParser->getClassUses(__FILE__));
        $list = $parser->parse($annotation, sprintf("class %s", get_class($this)));
        $this->assertInstanceOf(FooFixtures::class, $list[0]->instance);
    }

    /**
     * @expectedException \LilleBitte\Annotations\Exception\SyntaxErrorException
     */
    public function testCanThrowExceptionWhileTokenAssertionHasFailed()
    {
        try {
            $annotation = "/**\n * @FooFixtures(10\n */";
            $parser = new DocParser();
            $parser->setClassUses($this->phpFileParser->getClassUses(__FILE__));
            $parser->parse($annotation, sprintf("class %s", get_class($this)));
        } catch (\LilleBitte\Annotations\Exception\SyntaxErrorException $se) {
            throw $se;
        }
    }
}
