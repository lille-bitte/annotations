<?php

declare(strict_types=1);

namespace LilleBitte\Annotations\Tests\Fixtures;

use LilleBitte\Annotations\Tests\Fixtures\Foo as FooFixtures;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class Bar
{
    /**
     * @FooFixtures
     */
    private $foo;
}
