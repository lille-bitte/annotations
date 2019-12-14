<?php

declare(strict_types=1);

namespace LilleBitte\Annotations;

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

    public function __construct($contents)
    {
        $this->reset();
        $this->parseToken($contents);
    }

    /**
     * Parse buffer input to a list of tokens.
     *
     * @param string $contents Buffer input.
     * @return void
     */
    private function parseToken($contents)
    {
        $this->token = token_get_all($contents);

        foreach ($this->token as $key => $value) {
            if (!is_array($value) || $value[0] === \T_WHITESPACE) {
                unset($this->token[$key]);
            }
        }

        $this->token = array_values($this->token);
    }

    /**
     * Reset tokenizer metadata.
     *
     * @return void
     */
    private function reset()
    {
        $this->token = null;
        $this->current = null;
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
            $token = $this->getToken();
            $token = $token[0];
        }

        return null === $token
            ? null
            : $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenValue()
    {
        $token = $this->getToken();

        return null === $token
            ? null
            : $token[1];
    }
}
