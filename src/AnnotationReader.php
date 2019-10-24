<?php

namespace LilleBitte\Annotations;

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
			: null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClassAnnotations(\ReflectionClass $class)
	{
		return $this->parser->parse(
			$class->getDocComment(),
			sprintf("class %s", $class->getName())
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClassAnnotation(\ReflectionClass $class, $name)
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
	public function getMethodAnnotations(\ReflectionMethod $method)
	{
		$class = $method->getDeclaringClass();
		$context = sprintf(
			"method %s::%s",
			$class->getName(),
			$method->getName()
		);

		return $this->parser->parse(
			$method->getDocComment(),
			$context
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMethodAnnotation(\ReflectionMethod $method, $name)
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
	public function getPropertyAnnotations(\ReflectionProperty $property)
	{
		$class = $property->getDeclaringClass();
		$context = sprintf(
			"property %s::\$%s",
			$class->getName(),
			$property->getName()
		);

		return $this->parser->parse(
			$property->getDocComment(),
			$context
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPropertyAnnotation(\ReflectionProperty $property, $name)
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
