<?php

declare(strict_types=1);

namespace LilleBitte\Annotations\Tests;

use LilleBitte\Annotations\DocLexer;
use PHPUnit\Framework\TestCase;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class DocLexerTest extends TestCase
{
    private $docComments;

    protected function setUp()
    {
        $this->docComments = require __DIR__ . DIRECTORY_SEPARATOR . 'fixtures/doc_comment.php';
    }

    public function tearDown()
    {
        $this->docComments = null;
    }

    public function testCanGetTokenTypeIfSuppliedTokenIsNull()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[0]);
        $lexer->next();
        $this->assertNull($lexer->getTokenType());
    }

    public function testCanGetStringTypeToken()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[1]);
        $lexer->next();
        $this->assertEquals(DocLexer::T_STRING, $lexer->getTokenType());
    }

    public function testCanGetNumericTypeToken()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[2]);
        $lexer->next();
        $this->assertEquals(DocLexer::T_INTEGER, $lexer->getTokenType());
    }

    public function testCanGetFloatTypeToken()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[3]);
        $lexer->next();
        $this->assertEquals(DocLexer::T_FLOAT, $lexer->getTokenType());
    }

    public function testCanGetBooleanTrueToken()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[4]);
        $lexer->next();
        $this->assertEquals(DocLexer::T_TRUE, $lexer->getTokenType());
    }

    public function testCanGetBooleanFalseToken()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[5]);
        $lexer->next();
        $this->assertEquals(DocLexer::T_FALSE, $lexer->getTokenType());
    }

    public function testCanGetTokenNameWithNullToken()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[1]);
        $lexer->next();
        $this->assertEquals("string", $lexer->getTokenName());
    }

    public function testCannotGetTokenNameWithNullToken()
    {
        $lexer = new DocLexer();
        $this->assertNull($lexer->serializeType(1337));
    }
}
