<?php

declare(strict_types=1);

namespace LilleBitte\Annotations\Tests;

use LilleBitte\Annotations\ClassRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class ClassRegistryTest extends TestCase
{
	public function testCannotGetClass()
	{
		$this->assertNull(ClassRegistry::get("Foo\\Bar\\Baz"));
	}

	public function testCanGetEmptyNamespace()
	{
		ClassRegistry::register(\SplQueue::class);
		$this->assertTrue(true);
	}
}
