<?php

declare(strict_types=1);

namespace LilleBitte\Annotations;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
trait ReaderTrait
{
    /**
     * @var array
     */
    private $ignoredAnnotationNames = [];

    /**
     * {@inheritdoc}
     */
    public function setIgnoredAnnotationNames(array $names)
    {
        $this->ignoredAnnotationNames = $names;
    }

    /**
     * {@inheritdoc}
     */
    public function getIgnoredAnnotationNames()
    {
        return $this->ignoredAnnotationNames;
    }
}
