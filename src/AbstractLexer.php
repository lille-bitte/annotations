<?php

declare(strict_types=1);

namespace LilleBitte\Annotations;

use const PREG_SPLIT_NO_EMPTY;
use const PREG_SPLIT_DELIM_CAPTURE;

use function array_map;
use function array_values;
use function ctype_space;
use function preg_split;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
abstract class AbstractLexer
{
	/**
	 * @var array
	 */
	private $input;

	/**
	 * @var integer
	 */
	private $position;

	/**
	 * @var string
	 */
	protected $token;

	/**
	 * Reset all lexer context.
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->input = null;
		$this->token = null;
		$this->position = 0;
	}

	/**
	 * Feed lexer object with buffer.
	 *
	 * @param string $string The string to be lex.
	 * @return void
	 */
	public function setInput($string)
	{
		$this->reset();
		$this->transform($string);
	}

	/**
	 * Transform input buffer to list
	 * of tokens.
	 *
	 * @param string $string Input buffer.
	 * @return void
	 */
	private function transform($string)
	{
		$this->input = preg_split(
			$this->getPattern(),
			$string,
			-1,
			PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
		);

		$this->normalizeInput();
	}

	/**
	 * Normalize token lists by adding type
	 * metadata and purging whitespace.
	 *
	 * @return void
	 */
	private function normalizeInput()
	{
		foreach ($this->input as $k => $v) {
			if (ctype_space($v)) {
				unset($this->input[$k]);
			}
		}

		$this->input = array_map(
			function($v) {
				return [
					'type' => $this->getTokenType($v),
					'value' => $v
				];
			},
			array_values($this->input)
		);
	}

	/**
	 * Move into next token.
	 *
	 * @return boolean
	 */
	public function next(): bool
	{
		$this->token = isset($this->input[$this->position])
			? $this->input[$this->position++]
			: null;

		return $this->token !== null;
	}

	/**
	 * Reset stream counter.
	 *
	 * @return void
	 */
	public function rewind()
	{
		$this->position = 0;
	}

	/**
	 * Check if next token type is match against
	 * given token type.
	 *
	 * @param integer $type Token type.
	 * @return boolean
	 */
	public function isNextToken($type): bool
	{
		return $type === $this->nextTokenType();
	}

	/**
	 * Get next token type.
	 *
	 * @return integer|null
	 */
	public function nextTokenType()
	{
		if (!isset($this->input[$this->position])) {
			return null;
		}

		$token = $this->input[$this->position];
		$type = $this->getTokenType($token['value']);

		return $type;
	}

	/**
	 * Get current token metadata in
	 * current position + 1
	 *
	 * @return array|null
	 */
	public function peek()
	{
		if (!isset($this->input[$this->position + 1])) {
			return null;
		}

		return $this->input[$this->position + 1];
	}

	/**
	 * Get current token type in
	 * current position + 1
	 *
	 * @return integer|null
	 */
	public function peekType()
	{
		$val = $this->peek();

		return null !== $val
			? $val['type']
			: null;
	}

	/**
	 * Get current token value in
	 * current position + 1
	 *
	 * @return string|null
	 */
	public function peekValue()
	{
		$val = $this->peek();

		return null != $val
			? $val['value']
			: null;
	}

	/**
	 * Get current position.
	 *
	 * @return integer
	 */
	public function getPosition(): int
	{
		return $this->position;
	}

	/**
	 * Get current token value
	 *
	 * @return string|null
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * Get current token value.
	 *
	 * @return string
	 */
	public function getTokenValue(): string
	{
		return $this->token['value'];
	}

	/**
	 * Get token type.
	 *
	 * @param string|null $token Token.
	 * @return integer|null
	 */
	abstract public function getTokenType($token = null);

	/**
	 * Get token name.
	 *
	 * @param string|null $token Token.
	 * @return string|null
	 */
	abstract public function getTokenName($token = null);

	/**
	 * Get lexing pattern.
	 *
	 * @return string
	 */
	abstract public function getPattern(): string;
}
