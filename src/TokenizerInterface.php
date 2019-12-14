<?php

declare(strict_types=1);

namespace LilleBitte\Annotations;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface TokenizerInterface extends LexerInterface
{
    /**
     * Get current token value.
     *
     * @return mixed|null
     */
    public function getTokenValue();
}
