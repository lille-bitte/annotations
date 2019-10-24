<?php

namespace LilleBitte\Annotations;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface ReaderInterface
{
	/**
	 * Get class annotations.
	 *
	 * @param \ReflectionClass $class From which the class annotations
	 *                                should be read.
	 * @return array
	 */
	public function getClassAnnotations(\ReflectionClass $class);

	/**
	 * Get a class annotation.
	 *
	 * @param \ReflectionClass $class From which the class annotations
	 *                                should be read.
	 * @param string           $name  Name of the annotation.
	 * @return object|null
	 */
	public function getClassAnnotation(\ReflectionClass $class, $name);

	/**
	 * Get method annotations.
	 *
	 * @param \ReflectionMethod $method From which method the annotation
	 *                                  should be read.
	 * @return array
	 */
	public function getMethodAnnotations(\ReflectionMethod $method);

	/**
	 * Get a method annotation.
	 *
	 * @param \ReflectionMethod $method From which method the annotation
	 *                                  should be read.
	 * @param string            $name   Name of the annotation.
	 * @return object|null
	 */
	public function getMethodAnnotation(\ReflectionMethod $method, $name);

	/**
	 * Get property annotations.
	 *
	 * @param \ReflectionProperty $property From which property the annotation
	 *                                      should be read.
	 * @return array
	 */
	public function getPropertyAnnotations(\ReflectionProperty $property);

	/**
	 * Get a property annotation.
	 *
	 * @param \ReflectionProperty $property From which property the annotation
	 *                                      should be read.
	 * @param string              $name     Name of the annotation.
	 * @return object|null
	 */
	public function getPropertyAnnotation(\ReflectionProperty $property, $name);	
}