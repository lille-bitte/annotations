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
    use ReaderTrait;

    /**
     * @var DocParser
     */
    private $parser;

    /**
     * @var array
     */
    private $globalIgnoredAnnotationNames = [
        'fix', 'fixme', 'override',
        // PHPDocumentor 1
        'abstract', 'access', 'code',
        'deprec', 'endcode', 'exception',
        'final', 'ingroup', 'inheritdoc',
        'inheritDoc', 'magic', 'name',
        'toc', 'tutorial', 'private',
        'static', 'staticvar', 'staticVar',
        'throw',
        // PHPDocumentor 2
        'api', 'author', 'category',
        'copyright', 'deprecated', 'example',
        'filesource', 'global', 'ignore',
        'internal', 'license', 'link',
        'method', 'package', 'param',
        'property', 'property-read', 'property-write',
        'return', 'see', 'since',
        'source', 'subpackage', 'throws',
        'todo', 'TODO', 'usedby',
        'uses', 'var', 'version',
        // PHPUnit
        'codeCoverageIgnore', 'codeCoverageIgnoreStart', 'codeCoverageIgnoreEnd',
        // PHPCheckStyle
        'SuppressWarnings',
        // PHPStorm
        'noinspection',
        // PEAR
        'package_version',
        // PlantUML
        'startuml', 'enduml',
        // Symfony 3.3 Cache Adapter
        'experimental',
        // Slevomat Coding Standard
        'phpcsSuppress',
        // PHP CodeSniffer
        'codingStandardsIgnoreStart', 'codingStandardsIgnoreEnd',
        // PHPStan
        'template', 'implements', 'extends',
        'use',
    ];

    /**
     * @param DocParser|null $parser
     */
    public function __construct(DocParser $parser = null)
    {
        $this->parser = null === $parser
            ? new DocParser
            : $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobalIgnoredAnnotationNames()
    {
        return $this->globalIgnoredAnnotationNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassAnnotations(ReflectionClass $class): array
    {
        $this->parser->setClassUses(getClassUses($class->getFileName()));
        $this->parser->setIgnoredAnnotationNames($this->mergeBothIgnoredAnnotationNames());

        return $this->parser->parse($class->getDocComment(), sprintf("class %s", $class->getName()));
    }

    /**
     * {@inheritdoc}
     */
    public function getClassAnnotation(ReflectionClass $class, $name)
    {
        $annotations = $this->getClassAnnotations($class);
        $context     = sprintf("class %s", $class->getName());

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
        $class   = $method->getDeclaringClass();
        $context = sprintf("method %s::%s", $class->getName(), $method->getName());

        $this->parser->setClassUses(getClassUses($class->getFileName()));
        $this->parser->setIgnoredAnnotationNames($this->mergeBothIgnoredAnnotationNames());

        return $this->parser->parse($method->getDocComment(), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodAnnotation(ReflectionMethod $method, $name)
    {
        $annotations = $this->getMethodAnnotations($method);
        $class       = $method->getDeclaringClass();
        $context     = sprintf("method %s::%s", $class->getName(), $method->getName());

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
        $class   = $property->getDeclaringClass();
        $context = sprintf("property %s::\$%s", $class->getName(),$property->getName());

        $this->parser->setClassUses(getClassUses($class->getFileName()));
        $this->parser->setIgnoredAnnotationNames($this->mergeBothIgnoredAnnotationNames());

        return $this->parser->parse($property->getDocComment(), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyAnnotation(ReflectionProperty $property, $name)
    {
        $annotations = $this->getPropertyAnnotations($property);
        $class       = $property->getDeclaringClass();
        $context     = sprintf("property %s::\$%s", $class->getName(), $property->getName());

        foreach ($annotations as $annotation) {
            if ($name === $annotation->class && $context === $annotation->context) {
                return $annotation->instance;
            }
        }

        return null;
    }

    /**
     * Normalize ignored annotation names by setting
     * all values to key, and boolean (true) as value
     *
     * @param array $names Ignored annotation names.
     * @return array
     */
    private function normalizeIgnoredAnnotationNames(array $names)
    {
        return array_fill_keys($names, true);
    }

    /**
     * Merge global and common ignored annotation names.
     *
     * @return array
     */
    private function mergeBothIgnoredAnnotationNames()
    {
        return array_merge(
            $this->normalizeIgnoredAnnotationNames($this->getGlobalIgnoredAnnotationNames()),
            $this->normalizeIgnoredAnnotationNames($this->getIgnoredAnnotationNames())
        );
    }
}
