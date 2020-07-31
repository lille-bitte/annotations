<?php

declare(strict_types=1);

namespace LilleBitte\Annotations;

use const T_AS;
use const T_NAMESPACE;
use const T_NS_SEPARATOR;
use const T_STRING;
use const T_USE;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
final class PhpFileParser
{
    /**
     * @var TokenizerInterface
     */
    private $tokenizer;

    public function __construct(TokenizerInterface $tokenizer = null)
    {
        $this->tokenizer = $tokenizer ?? new InternalTokenizer();
    }

    /**
     * Get all namespaces in current PHP file.
     *
     * @param string $file PHP file name.
     * @return array
     */
    public function getNamespaces($file)
    {
        $res = [];

        $this->tokenizer->setContent($this->readFile($file));
        $this->tokenizer->refresh();
        $this->tokenizer->next();

        while (null !== $this->tokenizer->getToken()) {
            if ($this->tokenizer->getTokenType() === T_NAMESPACE) {
                $res[] = $this->getNamespaceFromCurrentTokenizer($this->tokenizer);
                continue;
            }

            $this->tokenizer->next();
        }

        return $res;
    }

    /**
     * Get all class uses in current PHP file.
     *
     * @param string $file PHP file name.
     * @return array
     */
    public function getClassUses($file)
    {
        $res = [];

        $this->tokenizer->setContent($this->readFile($file));
        $this->tokenizer->refresh();
        $this->tokenizer->next();

        while (null !== $this->tokenizer->getToken()) {
            if ($this->tokenizer->getTokenType() === T_USE) {
                $res[] = $this->getClassUsesFromCurrentTokenizer($this->tokenizer);
                continue;
            }

            $this->tokenizer->next();
        }

        return $res;
    }

    /**
     * Read contents of a PHP file.
     *
     * @param string $file PHP file name.
     * @return string
     */
    private function readFile($file)
    {
        if (!is_file($file)) {
            throw AnnotationException::invalidArgument(
                __FUNCTION__,
                sprintf("File '%s' not exists.", $file)
            );
        }

        $fileObject = new \SplFileObject($file);

        if (!is_a($fileObject, \SplFileObject::class)) {
            throw AnnotationException::invalidArgument(
                __FUNCTION__,
                sprintf(
                    "File '%s' has opened in write-only mode, or invalid permission.",
                    $file
                )
            );
        }

        $buf = '';

        while (!$fileObject->eof()) {
            $buf .= $fileObject->fgets();
        }

        return $buf;
    }

    /**
     * Get single namespace from given tokenizer object.
     *
     * @param TokenizerInterface $tokenizer Tokenizer object.
     * @return string
     */
    private function getNamespaceFromCurrentTokenizer(TokenizerInterface $tokenizer)
    {
        $buf = '';

        while ((false !== $tokenizer->next()) &&
               ($tokenizer->getTokenType() === T_STRING || $tokenizer->getTokenType() === T_NS_SEPARATOR)) {
            $buf .= $tokenizer->getTokenValue();
        }

        return $buf;
    }

    /**
     * Get single class uses from given tokenizer object.
     *
     * @param TokenizerInterface $tokenizer Tokenizer object.
     * @return array
     */
    private function getClassUsesFromCurrentTokenizer(TokenizerInterface $tokenizer)
    {
        $retVal = [
            'value' => '',
            'class' => '',
            'alias' => ''
        ];

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

                $retVal['alias'] = $tokenizer->getTokenValue();
                $retVal['class'] = '';
                continue;
            }

            $tokenValue       = $tokenizer->getTokenValue();
            $retVal['value'] .= $tokenValue;
            $retVal['class']  = $tokenValue;
        }

        return $retVal;
    }
}
