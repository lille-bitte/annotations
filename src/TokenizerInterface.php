<?php

namespace LilleBitte\Annotations;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface TokenizerInterface
{
	/**
	 * Move internal iteration pointer next.
	 *
	 * @return void
	 */
	public function next();

	/**
	 * Get current token metadata.
	 *
	 * @return array|null
	 */
	public function getToken();

	/**
	 * Get current token type.
	 *
	 * @return integer|null
	 */
	public function getTokenType();

	/**
	 * Get current token value.
	 *
	 * @return mixed|null
	 */
	public function getTokenValue();
}
