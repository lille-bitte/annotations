<?php

declare(strict_types=1);

namespace LilleBitte\Annotations\Tests;

use LilleBitte\Annotations\AbstractLexer;
use LilleBitte\Annotations\DocLexer;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class AbstractLexerTest extends TestCase
{
    /**
     * @var array
     */
    private $docComments;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->docComments = require __DIR__ . DIRECTORY_SEPARATOR . 'fixtures/doc_comment.php';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->docComments = null;
    }

    public function testInstanceOf()
    {
        $lexer = new DocLexer();
        $this->assertInstanceOf(AbstractLexer::class, $lexer);
    }

    public function testCanResetLexerContext()
    {
        $lexer = new DocLexer();
        $lexer->reset();
        $this->assertNull($lexer->getToken());
    }

    public function testSetInputToLexer()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[0]);
        $this->assertTrue(true);
    }

    public function testCannotMoveToNextToken()
    {
        $lexer = new DocLexer();
        $lexer->next();
        $this->assertNull($lexer->getToken());
    }

    public function testCanMoveToNextToken()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[0]);
        $lexer->next();
        $this->assertNotNull($lexer->getToken());
    }

    public function testCanRewindTokenPosition()
    {
        $lexer = new DocLexer();
        $lexer->rewind();
        $this->assertTrue(true);
    }

    public function testIfNextTokenIsMatch()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[0]);
        $this->assertFalse($lexer->isNextToken(DocLexer::T_START_ANNOTATION));
    }

    public function testCannotGetNextTokenType()
    {
        $lexer = new DocLexer();
        $this->assertNull($lexer->nextTokenType());
    }

    public function testCannotPeek()
    {
        $lexer = new DocLexer();
        $this->assertNull($lexer->peek());
    }

    public function testCanPeek()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[0]);
        $this->assertNotNull($lexer->peek());
    }

    public function testCannotGetPeekedTokenType()
    {
        $lexer = new DocLexer();
        $this->assertNull($lexer->peekType());
    }

    public function testCanGetPeekedTokenType()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[0]);

        for ($i = 0; $i < 3; $i++) {
            $lexer->next();
        }

        $this->assertNotNull($lexer->peekType());
    }

    public function testCannotGetPeekedToken()
    {
        $lexer = new DocLexer();
        $this->assertNull($lexer->peekValue());
    }

    public function testCanGetPeekedToken()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[0]);
        $this->assertNotNull($lexer->peekValue());
    }

    public function testCanGetTokenPosition()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[0]);
        $this->assertEquals(0, $lexer->getPosition());
    }

    public function testCanGetTokenValue()
    {
        $lexer = new DocLexer();
        $lexer->setInput($this->docComments[0]);
        $lexer->next();
        $this->assertEquals('/', $lexer->getTokenValue());
    }
}
