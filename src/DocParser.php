<?php

namespace LilleBitte\Annotations;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class DocParser
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
	private $ignoredAnnotationNames = [
		'var', 'author', 'param', 'return'
	];

	/**
	 * @var array
	 */
	private $uses;

	public function __construct(AbstractLexer $lexer = null)
	{
		$this->lexer = (null === $lexer)
			? new DocLexer
			: $lexer;
	}

	/**
	 * Parse given docblock.
	 *
	 * @param string $input Docblock comment.
	 * @param string $context Parsing context.
	 * @return array
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
	 */
	private function process()
	{
		$this->assert(DocLexer::T_START_ANNOTATION, __METHOD__, '@');

		$names = $this->parseDirective();

		if ($this->isIgnoredAnnotation($names)) {
			return null;
		}

		$tmp = \explode("\\", $names);

		// match for an alias.
		foreach ($this->uses as $el) {
			if ($tmp[0] === $el['alias']) {
				$names = sprintf(
					"%s%s",
					$el['value'],
					count($tmp) === 1
						? ''
						: "\\" . \join("\\", \array_values(\array_slice($tmp, 1)))
				);

				break;
			}
		}

		if (!class_exists($names)) {
			throw AnnotationException::classNotExists(
				__METHOD__,
				$names
			);
		}

		$param = $this->parseParenthesis();

		$parameters = null === $param
			? [null]
			: \array_merge(
				empty($param['parameters']) ? [null] : $param['parameters'],
				empty($param['named-parameters']) ? [null] : [$param['named-parameters']]
			);

		$instance = (new \ReflectionClass($names))
			->newInstanceArgs($parameters);

		if (!($instance instanceof $names)) {
			throw AnnotationException::runtime(
				__METHOD__,
				sprintf(
					"Failed to get an instance of [%s]",
					$names
				)
			);
		}

		$ret = new \stdClass;
		$ret->class = $names;
		$ret->context = $this->getContext();
		$ret->instance = $instance;

		return $ret;
	}

	/**
	 *
	 */
	public function setClassUses($uses)
	{
		$this->uses = $uses;
	}

	/**
	 *
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
	 */
	private function parseAssignment()
	{
		$this->lexer->next();

		$key = $this->lexer->getTokenValue();

		$this->assert(DocLexer::T_ASSIGN, __METHOD__, '=');

		$token = $this->parseLiteral();

		$ret = new \stdClass;
		$ret->field = $key;
		$ret->value = $token;

		return $ret;
	}

	/**
	 * ABNF: (Literal ::= string / integer / boolean / Array)
	 *
	 * @return mixed
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

		$res[] = $this->parseLiteral();

		while ($this->lexer->isNextToken(DocLexer::T_COMMA)) {
			$this->assert(DocLexer::T_COMMA, __METHOD__, ',');

			if ($this->lexer->isNextToken(DocLexer::T_CLOSE_CURLY_BRACES)) {
				break;
			}

			$res[] = $this->parseLiteral();
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
	 * @param string $buf Annotation value.
	 * @return boolean
	 */
	private function isIgnoredAnnotation($buf)
	{
		return in_array($buf, $this->ignoredAnnotationNames, true);
	}
}
