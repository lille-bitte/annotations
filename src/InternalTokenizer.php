<?php

declare(strict_types=1);

namespace LilleBitte\Annotations;

use const T_WHITESPACE;

use function array_values;
use function is_array;
use function token_get_all;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class InternalTokenizer implements TokenizerInterface
{
    /**
     * @var array|null
     */
    private $token;

    /**
     * @var array
     */
    private $current;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var string
     */
    private $content;

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * @param string $content Input content.
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Parse buffer input to a list of tokens.
     *
     * @param string $contents Buffer input.
     * @return void
     */
    private function parseToken($contents)
    {
        $this->token = $this->normalizeParsedToken(token_get_all($contents));
    }

    /**
     * Refresh list of parsed tokens.
     *
     * @return void
     */
    public function refresh()
    {
        $this->reset();
        $this->parseToken($this->getContent());
    }

    /**
     * Normalize currently parsed token.
     *
     * @param array $tokens Currently parsed tokens.
     * @return array
     */
    private function normalizeParsedToken($tokens)
    {
        foreach ($tokens as $key => $value) {
            if (!is_array($value) || $value[0] === T_WHITESPACE) {
                unset($tokens[$key]);
            }
        }

        return array_values($tokens);
    }

    /**
     * Reset tokenizer metadata.
     *
     * @return void
     */
    private function reset()
    {
        $this->token    = null;
        $this->current  = null;
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->current = !isset($this->token[$this->position])
            ? null
            : $this->token[$this->position++];

        return null !== $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenType($token = null)
    {
        if (null === $token) {
            $token = $this->getToken()[0];
        }

        return null === $token ? null : $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenValue()
    {
        $token = $this->getToken();
        return null === $token ? null : $token[1];
    }
}
