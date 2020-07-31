<?php

declare(strict_types=1);

namespace LilleBitte\Annotations;

use ReflectionClass;
use stdClass;

use function array_merge;
use function array_slice;
use function array_values;
use function class_exists;
use function count;
use function explode;
use function floatval;
use function is_object;
use function in_array;
use function intval;
use function join;
use function ltrim;
use function rtrim;
use function sprintf;
use function substr;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
final class DocParser
{
    /**
     * @var DocLexer
     */
    private $lexer;

    /**
     * @var string
     */
    private $context;

    /**
     * @var array
     */
    private $ignoredAnnotationNames = [];

    /**
     * @var array
     */
    private $uses = [];

    /**
     * @param AbstractLexer|null $lexer
     */
    public function __construct(AbstractLexer $lexer = null)
    {
        $this->lexer = (null === $lexer)
            ? new DocLexer
            : $lexer;
    }

    /**
     * Set ignored annotation names.
     *
     * @param array $names Ignored annotation names.
     * @return void
     */
    public function setIgnoredAnnotationNames($names)
    {
        $this->ignoredAnnotationNames = $names;
    }

    /**
     * Get ignored annotation names.
     *
     * @return array
     */
    public function getIgnoredAnnotationNames()
    {
        return $this->ignoredAnnotationNames;
    }

    /**
     * Parse given docblock.
     *
     * @param string $input Docblock comment.
     * @param string $context Parsing context.
     * @return array
     * @throws Exception\ClassNotExistsException
     * @throws Exception\SyntaxErrorException
     * @throws \ReflectionException
     */
    public function parse($input, $context): array
    {
        $this->lexer->setInput($input);

        // make sure first call of getToken()
        // is not null..
        $this->lexer->next();

        $this->setContext($context);
        return $this->aggregate();
    }

    /**
     * Set parsing context.
     *
     * @param string $context Context name.
     * @return void
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Get parsing context.
     *
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * Populate parsing results.
     *
     * @return array
     * @throws Exception\ClassNotExistsException
     * @throws Exception\SyntaxErrorException
     * @throws \ReflectionException
     */
    private function aggregate(): array
    {
        $res = [];

        while (null !== $this->lexer->getToken()) {
            if (!$this->lexer->isNextToken(DocLexer::T_START_ANNOTATION)) {
                $this->lexer->next();
                continue;
            }

            $ret = $this->process();

            if (null === $ret) {
                continue;
            }

            $res[] = $ret;
        }

        return $res;
    }

    /**
     * Group each token to one entity.
     *
     * @return object
     * @throws Exception\ClassNotExistsException
     * @throws Exception\SyntaxErrorException
     * @throws \ReflectionException
     */
    private function process()
    {
        $this->assert(DocLexer::T_START_ANNOTATION, __METHOD__, '@');

        $names = $this->parseDirective();

        if ($this->isIgnoredAnnotation($names)) {
            return null;
        }

        $tmp     = explode("\\", $names);
        $aliased = false;
        $matched = false;

        // match for an alias.
        foreach ($this->uses as $el) {
            if ($tmp[count($tmp) - 1] === $el['alias']) {
                $names   = $el['value'];
                $aliased = true;
                break;
            }
        }

        if (!$aliased) {
            foreach ($this->uses as $el) {
                $splitted  = explode("\\", $el['value']);
                $className = $splitted[count($splitted) - 1];

                if ($tmp[count($tmp) - 1] === $className) {
                    $matched = true;
                    break;
                }
            }
        }

        if (!$matched && !$aliased) {
            throw new \RuntimeException(
                sprintf(
                    "Annotation @%s did not exist. Did you forget to import " .
                    "associated class or namespace alias?",
                    $tmp[count($tmp) - 1]
                )
            );
        }

        $param      = $this->parseParenthesis();
        $parameters = null === $param
            ? [null]
            : array_merge(
                empty($param['parameters']) ? [] : $param['parameters'],
                empty($param['named-parameters']) ? [] : [$param['named-parameters']]
            );

        foreach ($this->uses as $use) {
            if ($names === $use['alias'] || $names === $use['class']) {
                $actualFqcn = $use['value'];
                break;
            }
        }

        $refl     = new ReflectionClass(isset($actualFqcn) ? $actualFqcn : $names);
        $instance = $refl->hasMethod('__construct')
            ? $refl->newInstanceArgs($parameters)
            : $refl->newInstanceWithoutConstructor();

        $ret           = new stdClass;
        $ret->class    = $refl->getName();
        $ret->context  = $this->getContext();
        $ret->instance = $instance;

        return $ret;
    }

    /**
     * Set list of used class.
     *
     * @param array $uses List of used class.
     */
    public function setClassUses($uses = [])
    {
        $this->uses = $uses;
    }

    /**
     * Get list of used class.
     *
     * @return array
     */
    public function getClassUses()
    {
        return $this->uses;
    }

    /**
     * ABNF: (Directive ::= string)
     *
     * @return mixed
     */
    private function parseDirective()
    {
        $this->lexer->next();
        $val = $this->lexer->getToken();
        return $val['value'];
    }

    /**
     * ABNF: (Parenthesis (class instantiation) ::= '(' (*Literal) ')')
     *
     * @return array|null
     * @throws Exception\SyntaxErrorException
     */
    private function parseParenthesis()
    {
        $res = [
            'parameters' => [],
            'named-parameters' => []
        ];

        if (!$this->lexer->isNextToken(DocLexer::T_OPEN_PARENTHESIS)) {
            return null;
        }

        $this->assert(DocLexer::T_OPEN_PARENTHESIS, __METHOD__, '(');

        if ($this->lexer->isNextToken(DocLexer::T_CLOSE_PARENTHESIS)) {
            return $res;
        }

        $ret = $this->parseValue();

        if (is_object($ret) && $ret instanceof \stdClass) {
            $res['named-parameters'][$ret->field] = $ret->value;
        } else {
            $res['parameters'][] = $ret;
        }

        while ($this->lexer->isNextToken(DocLexer::T_COMMA)) {
            $this->assert(DocLexer::T_COMMA, __METHOD__, ',');

            if ($this->lexer->isNextToken(DocLexer::T_CLOSE_PARENTHESIS)) {
                break;
            }

            $ret = $this->parseValue();

            if (is_object($ret) && $ret instanceof \stdClass) {
                $res['named-parameters'][$ret->field] = $ret->value;
            } else {
                $res['parameters'][] = $ret;
            }
        }

        $this->assert(DocLexer::T_CLOSE_PARENTHESIS, __METHOD__, ')');
        return $res;
    }

    /**
     * ABNF: (Value ::= Literal / Assignment)
     *
     * @return mixed
     * @throws Exception\SyntaxErrorException
     */
    private function parseValue()
    {
        if (DocLexer::T_ASSIGN === $this->lexer->peekType()) {
            return $this->parseAssignment();
        }

        return $this->parseLiteral();
    }

    /**
     * ABNF: (Assignment ::= string '=' Literal)
     *
     * @return object
     * @throws Exception\SyntaxErrorException
     */
    private function parseAssignment()
    {
        $this->lexer->next();

        $key = $this->lexer->getTokenValue();

        $this->assert(DocLexer::T_ASSIGN, __METHOD__, '=');

        $token = $this->parseLiteral();

        $ret        = new \stdClass;
        $ret->field = $key;
        $ret->value = $token;

        return $ret;
    }

    /**
     * ABNF: (Literal ::= string / integer / boolean / Array)
     *
     * @return mixed
     * @throws Exception\SyntaxErrorException
     */
    private function parseLiteral()
    {
        if ($this->lexer->isNextToken(DocLexer::T_OPEN_CURLY_BRACES)) {
            return $this->parseArray();
        }

        switch ($this->lexer->nextTokenType()) {
            case DocLexer::T_STRING:
                $this->assert(
                    DocLexer::T_STRING,
                    __METHOD__,
                    $this->lexer->serializeType(DocLexer::T_STRING)
                );

                return ltrim(rtrim($this->lexer->getTokenValue(), '"'), '"');
            case DocLexer::T_INTEGER:
                $this->assert(
                    DocLexer::T_INTEGER,
                    __METHOD__,
                    $this->lexer->serializeType(DocLexer::T_INTEGER)
                );

                $token = $this->lexer->getTokenValue();
                $ret = $token[0] === '-'
                    ? intval(substr($token, 1)) * -1
                    : ($token[0] === '+'
                        ? intval(substr($token, 1))
                        : intval(substr($token, 0)));

                return $ret;
            case DocLexer::T_FLOAT:
                $this->assert(
                    DocLexer::T_FLOAT,
                    __METHOD__,
                    $this->lexer->serializeType(DocLexer::T_FLOAT)
                );

                $token = $this->lexer->getTokenValue();
                $ret = $token[0] === '-'
                    ? floatval(substr($token, 1)) * -1.0
                    : ($token[0] === '+'
                        ? floatval(substr($token, 1))
                        : floatval(substr($token, 0)));

                return $ret;
            case DocLexer::T_TRUE:
                $this->assert(
                    DocLexer::T_TRUE,
                    __METHOD__,
                    $this->lexer->serializeType(DocLexer::T_TRUE)
                );

                return true;
            case DocLexer::T_FALSE:
                $this->assert(
                    DocLexer::T_FALSE,
                    __METHOD__,
                    $this->lexer->serializeType(DocLexer::T_FALSE)
                );

                return false;
            default:
                throw AnnotationException::syntaxError(
                    __METHOD__,
                    "<null>"
                );
        }
    }

    /**
     * ABNF: (Array ::= '{' *(Literal [',']) '}')
     *
     * @return array
     * @throws Exception\SyntaxErrorException
     */
    private function parseArray()
    {
        $res = [];
        $this->assert(DocLexer::T_OPEN_CURLY_BRACES, __METHOD__, '{');

        // if next token adjacent to open curly
        // braces, return immediately
        if ($this->lexer->isNextToken(DocLexer::T_CLOSE_CURLY_BRACES)) {
            $this->assert(DocLexer::T_CLOSE_CURLY_BRACES, __METHOD__, '}');
            return $res;
        }

        if ($this->lexer->peekType() === DocLexer::T_COLON) {
            $this->lexer->next();
            $key = $this->lexer->getTokenValue();
            $this->assert(DocLexer::T_COLON, __METHOD__, ':');
        } else {
            $key = count($res);
        }

        $res[$key] = $this->parseLiteral();

        while ($this->lexer->isNextToken(DocLexer::T_COMMA)) {
            $this->assert(DocLexer::T_COMMA, __METHOD__, ',');

            if ($this->lexer->isNextToken(DocLexer::T_CLOSE_CURLY_BRACES)) {
                break;
            }

            if ($this->lexer->peekType() === DocLexer::T_COLON) {
                $this->lexer->next();
                $key = $this->lexer->getTokenValue();
                $this->assert(DocLexer::T_COLON, __METHOD__, ':');
            } else {
                $key = count($res);
            }

            $res[$key] = $this->parseLiteral();
        }

        $this->assert(DocLexer::T_CLOSE_CURLY_BRACES, __METHOD__, '}');
        return $res;
    }

    /**
     * Assert next token and move to next position
     * if match.
     *
     * @param integer $type Token type.
     * @param string $caller Caller name.
     * @param string $expected Expected result.
     * @return void
     * @throws Exception\SyntaxErrorException
     */
    private function assert($type, $caller, $expected)
    {
        if (!$this->lexer->isNextToken($type)) {
            throw AnnotationException::syntaxError(
                $caller,
                $expected
            );
        }

        // move into next token
        $this->lexer->next();
    }

    /**
     * Check if given annotation value is
     * on ignored list.
     *
     * @param string $name Annotation name.
     * @return boolean
     */
    private function isIgnoredAnnotation(string $name)
    {
        return isset($this->ignoredAnnotationNames[$name]);
    }
}
