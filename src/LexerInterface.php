<?php

declare(strict_types=1);

namespace LilleBitte\Annotations;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface LexerInterface
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
     * Get token type.
     *
     * @param string|null $token Token.
     * @return integer|null
     */
    public function getTokenType($token = null);
}
