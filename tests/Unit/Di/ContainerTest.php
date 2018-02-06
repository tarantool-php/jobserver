<?php

namespace App\Tests\Unit\Di;

use App\Di\Container;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    public function testGet(): void
    {
        $container = new Container(['foo' => 'bar'], 'test', true);

        self::assertSame('bar', $container->get('foo'));
    }

    public function testGetEnv(): void
    {
        $container = new Container([], 'test', true);

        self::assertSame('test', $container->getEnv());
    }

    public function testIsDebug(): void
    {
        $container = new Container([], 'test', true);

        self::assertTrue($container->isDebug());
    }

    public function testGetOptions(): void
    {
        $options = ['foo' => 'bar', 'baz' => 'qux'];
        $container = new Container($options, 'test', true);

        self::assertSame($options, $container->getOptions());
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

        self::assertSame(42, $container->tryGet('foo', 42));
    }
}
