<?php

namespace App\Tests\Unit\Di;

use App\Di\Container;
use App\Di\ContainerBuilder;
use PHPUnit\Framework\TestCase;

final class ContainerBuilderTest extends TestCase
{
    public function testConstructorSetOptions(): void
    {
        $options = ['foo' => 'bar'];
        $builder = new ContainerBuilder($options);

        self::assertSame($options['foo'], $builder->get('foo'));
    }

    public function testSetGetEnv(): void
    {
        $builder = new ContainerBuilder();
        $env = 'test';

        self::assertNotSame($env, $builder->getEnv());
        $builder->setEnv($env);
        self::assertSame($env, $builder->getEnv());
    }

    public function testSetGetDebug(): void
    {
        $builder = new ContainerBuilder();

        self::assertNotTrue($builder->isDebug());
        $builder->setDebug(true);
        self::assertTrue($builder->isDebug());
        $builder->setDebug(false);
        self::assertFalse($builder->isDebug());
    }

    public function testSetOptions(): void
    {
        $builder = new ContainerBuilder();
        $builder->setOptions(['foo' => 'bar']);

        self::assertSame('bar', $builder->get('foo'));
    }

    public function testGetOptions(): void
    {
        $options = ['foo' => 'bar', 'baz' => 'qux'];
        $builder = new ContainerBuilder($options);

        self::assertSame($options, $builder->getOptions());
    }

    public function testSet(): void
    {
        $builder = new ContainerBuilder();
        $builder->set('foo', 'bar');

        self::assertSame('bar', $builder->get('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown option "foo".
     */
    public function testGetMissingOption(): void
    {
        $builder = new ContainerBuilder();

        $builder->get('foo');
    }

    public function testTryGet(): void
    {
        $builder = new ContainerBuilder();

        self::assertSame(42, $builder->tryGet('foo', 42));
    }

    public function testBuild(): void
    {
        $builder = new ContainerBuilder(['foo' => 'bar']);
        $builder->setEnv('baz');
        $builder->setDebug(true);

        $container = $builder->build();

        self::assertInstanceOf(Container::class, $container);
        self::assertSame('bar', $container->get('foo'));
        self::assertSame('baz', $container->getEnv());
        self::assertTrue($container->isDebug());
    }
}
