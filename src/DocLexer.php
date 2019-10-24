<?php

namespace LilleBitte\Annotations;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class DocLexer extends AbstractLexer
{
	const T_INTEGER           = \T_LNUMBER;
	const T_FLOAT             = \T_DNUMBER;
	const T_STRING            = \T_CONSTANT_ENCAPSED_STRING;

	const T_START_ANNOTATION   = 100;
	const T_OPEN_PARENTHESIS   = 101;
	const T_CLOSE_PARENTHESIS  = 102;
	const T_SINGLE_QUOTE       = 103;
	const T_DOUBLE_QUOTE       = 104;
	const T_OPEN_CURLY_BRACES  = 105;
	const T_CLOSE_CURLY_BRACES = 106;
	const T_ASSIGN             = 107;
	const T_COMMA              = 108;
	const T_TRUE               = 109;
	const T_FALSE              = 110;

	/**
	 * @var array
	 */
	private $symbolToConst = [
		'@' => self::T_START_ANNOTATION,
		'(' => self::T_OPEN_PARENTHESIS,
		')' => self::T_CLOSE_PARENTHESIS,
		"'" => self::T_SINGLE_QUOTE,
		'"' => self::T_DOUBLE_QUOTE,
		'{' => self::T_OPEN_CURLY_BRACES,
		'}' => self::T_CLOSE_CURLY_BRACES,
		'=' => self::T_ASSIGN,
		',' => self::T_COMMA
	];

	/**
	 * @var array
	 */
	private $constToName = [
		self::T_START_ANNOTATION   => "start-annotation",
		self::T_OPEN_PARENTHESIS   => "open-parenthesis",
		self::T_CLOSE_PARENTHESIS  => "close-parenthesis",
		self::T_SINGLE_QUOTE       => "single-quote",
		self::T_DOUBLE_QUOTE       => "double-quote",
		self::T_OPEN_CURLY_BRACES  => "open-curly-brace",
		self::T_CLOSE_CURLY_BRACES => "close-curly-brace",
		self::T_ASSIGN             => "assign",
		self::T_COMMA              => "comma",
		self::T_STRING             => "string",
		self::T_INTEGER            => "integer",
		self::T_FLOAT              => "float",
		self::T_TRUE               => "boolean (true)",
		self::T_FALSE              => "boolean (false)"
	];

	/**
	 * {@inheritdoc}
	 */
	public function getTokenType($token = null)
	{
		if ($token === null) {
			$token = $this->getToken();
			$token = $token['value'];
		}

		if ($token[0] === '"' && $token[strlen($token) - 1] === '"') {
			return self::T_STRING;
		}

		if ((($token[0] === '+' || $token[0] === '-') && \ctype_digit(\substr($token, 1))) ||
			\ctype_digit($token)) {
			return self::T_INTEGER;
		}

		if ($token === "true") {
			return self::T_TRUE;
		}

		if ($token === "false") {
			return self::T_FALSE;
		}

		return isset($this->symbolToConst[$token])
			? $this->symbolToConst[$token]
			: null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTokenName($token = null)
	{
		$type = null === $token
			? $this->getTokenType()
			: $this->getTokenType($token);

		return null !== $type
			? $this->serializeType($type)
			: null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function serializeType($type)
	{
		return !isset($this->constToName[$type])
			? null
			: $this->constToName[$type];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPattern()
	{
		return '/([a-z\\\\_][a-z_\\\\]*)|([\+\-]?[0-9]+)|("(?:\"\"|[^\"])*+")|(.)|\s+|\*+/im';
	}
}
