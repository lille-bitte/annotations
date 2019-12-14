<?php

declare(strict_types=1);

namespace LilleBitte\Annotations;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use function sprintf;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class AnnotationReader implements ReaderInterface
{
    /**
     * @var DocParser
     */
    private $parser;

    public function __construct(DocParser $parser = null)
    {
        $this->parser = null === $parser
            ? new DocParser
            : $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassAnnotations(ReflectionClass $class): array
    {
        $this->parser->setClassUses(
            \LilleBitte\Annotations\getClassUses($class->getFileName())
        );

        return $this->parser->parse(
            $class->getDocComment(),
            sprintf("class %s", $class->getName())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getClassAnnotation(ReflectionClass $class, $name)
    {
        $annotations = $this->getClassAnnotations($class);
        $context = sprintf(
            "class %s",
            $class->getName()
        );

        foreach ($annotations as $annotation) {
            if ($name === $annotation->class && $annotation->context === $context) {
                return $annotation->instance;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodAnnotations(ReflectionMethod $method): array
    {
        $class = $method->getDeclaringClass();
        $context = sprintf(
            "method %s::%s",
            $class->getName(),
            $method->getName()
        );

        $this->parser->setClassUses(
            \LilleBitte\Annotations\getClassUses($class->getFileName())
        );

        return $this->parser->parse(
            $method->getDocComment(),
            $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodAnnotation(ReflectionMethod $method, $name)
    {
        $annotations = $this->getMethodAnnotations($method);
        $class = $method->getDeclaringClass();
        $context = sprintf(
            "method %s::%s",
            $class->getName(),
            $method->getName()
        );

        foreach ($annotations as $annotation) {
            if ($name === $annotation->class && $context === $annotation->context) {
                return $annotation->instance;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyAnnotations(ReflectionProperty $property): array
    {
        $class = $property->getDeclaringClass();
        $context = sprintf(
            "property %s::\$%s",
            $class->getName(),
            $property->getName()
        );

        $this->parser->setClassUses(
            \LilleBitte\Annotations\getClassUses($class->getFileName())
        );

        return $this->parser->parse(
            $property->getDocComment(),
            $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyAnnotation(ReflectionProperty $property, $name)
    {
        $annotations = $this->getPropertyAnnotations($property);
        $class = $property->getDeclaringClass();
        $context = sprintf(
            "property %s::\$%s",
            $class->getName(),
            $property->getName()
        );

        foreach ($annotations as $annotation) {
            if ($name === $annotation->class && $context === $annotation->context) {
                return $annotation->instance;
            }
        }

        return null;
    }
}
