<?php

declare(strict_types=1);

namespace LilleBitte\Annotations;

/**
 * Read contents of a file.
 *
 * @param string $file File name.
 * @return string
 */
function readFile($file): string
{
    if (!is_file($file)) {
        throw AnnotationException::invalidArgument(
            __FUNCTION__,
            sprintf(
                "file '%s' not exists.",
                $file
            )
        );
    }

    $fobj = new \SplFileObject($file);

    if (!($fobj instanceof \SplFileObject)) {
        throw AnnotationException::invalidArgument(
            __FUNCTION__,
            sprintf(
                "file '%s' has opened in write-only mode, or invalid permission.",
                $file
            )
        );
    }

    $buf = '';

    while (!$fobj->eof()) {
        $buf .= $fobj->fgets();
    }

    return $buf;
}

/**
 * Get list of a namespaces from
 * a file.
 *
 * @param string $file File name.
 * @return array
 */
function getNamespaces($file): array
{
    $tokenizer = new InternalTokenizer(
        readFile($file)
    );

    $res = [];

    $callback = function () use ($tokenizer) {
        $buf = '';

        while ((false !== $tokenizer->next()) &&
               ($tokenizer->getTokenType() === T_STRING || $tokenizer->getTokenType() === T_NS_SEPARATOR)) {
            $buf .= $tokenizer->getTokenValue();
        }

        return $buf;
    };

    // move to next token
    $tokenizer->next();

    while (null !== $tokenizer->getToken()) {
        if ($tokenizer->getTokenType() === T_NAMESPACE) {
            $res[] = $callback();
            continue;
        }

        $tokenizer->next();
    }

    return $res;
}

/**
 * Get list of a used class from
 * a file.
 *
 * @param string $file File name.
 * @return array
 */
function getClassUses($file): array
{
    $tokenizer = new InternalTokenizer(
        readFile($file)
    );

    $res = [];

    $callback = function () use ($tokenizer) {
        $ret = ['value' => '', 'alias' => null];

        while ((false !== $tokenizer->next()) &&
               ($tokenizer->getTokenType() === T_STRING || $tokenizer->getTokenType() === T_NS_SEPARATOR ||
                $tokenizer->getTokenType() === T_AS)) {
            if ($tokenizer->getTokenType() === T_AS) {
                $tokenizer->next();

                if ($tokenizer->getTokenType() !== T_STRING) {
                    throw AnnotationException::syntaxError(
                        __METHOD__,
                        "string"
                    );
                }

                $ret['alias'] = $tokenizer->getTokenValue();
                continue;
            }

            $ret['value'] .= $tokenizer->getTokenValue();
        }

        return $ret;
    };

    // move to next token
    $tokenizer->next();

    while (null !== $tokenizer->getToken()) {
        if ($tokenizer->getTokenType() === T_USE) {
            $res[] = $callback();
            continue;
        }

        $tokenizer->next();
    }

    return $res;
}
