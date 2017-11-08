<?php

namespace App\Tests\Unit\Di;

use App\Di\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testGet(): void
    {
        $container = new Container(['foo' => 'bar'], 'test', true);

        $this->assertSame('bar', $container->get('foo'));
    }

    public function testGetEnv(): void
    {
        $container = new Container([], 'test', true);

        $this->assertSame('test', $container->getEnv());
    }

    public function testIsDebug(): void
    {
        $container = new Container([], 'test', true);

        $this->assertTrue($container->isDebug());
    }

    public function testGetOptions(): void
    {
        $options = ['foo' => 'bar', 'baz' => 'qux'];
        $container = new Container($options, 'test', true);

        $this->assertEquals($options, $container->getOptions());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown option "foo".
     */
    public function testGetMissingOption(): void
    {
        $container = new Container([], 'test', true);

        $container->get('foo');
    }

    public function testTryGet(): void
    {
        $container = new Container([], 'test', true);

        $this->assertSame(42, $container->tryGet('foo', 42));
    }
}
