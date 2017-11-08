<?php

namespace App\Tests\Unit\Di;

use App\Di\Container;
use App\Di\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class ContainerBuilderTest extends TestCase
{
    public function testConstructorSetOptions(): void
    {
        $options = ['foo' => 'bar'];
        $builder = new ContainerBuilder($options);

        $this->assertSame($options['foo'], $builder->get('foo'));
    }

    public function testSetGetEnv(): void
    {
        $builder = new ContainerBuilder();
        $env = 'test';

        $this->assertNotSame($env, $builder->getEnv());
        $builder->setEnv($env);
        $this->assertSame($env, $builder->getEnv());
    }

    public function testSetGetDebug(): void
    {
        $builder = new ContainerBuilder();

        $this->assertNotTrue($builder->isDebug());
        $builder->setDebug(true);
        $this->assertTrue($builder->isDebug());
        $builder->setDebug(false);
        $this->assertFalse($builder->isDebug());
    }

    public function testSetOptions(): void
    {
        $builder = new ContainerBuilder();
        $builder->setOptions(['foo' => 'bar']);

        $this->assertSame('bar', $builder->get('foo'));
    }

    public function testGetOptions(): void
    {
        $options = ['foo' => 'bar', 'baz' => 'qux'];
        $builder = new ContainerBuilder($options);

        $this->assertEquals($options, $builder->getOptions());
    }

    public function testSet(): void
    {
        $builder = new ContainerBuilder();
        $builder->set('foo', 'bar');

        $this->assertSame('bar', $builder->get('foo'));
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

        $this->assertSame(42, $builder->tryGet('foo', 42));
    }

    public function testBuild(): void
    {
        $builder = new ContainerBuilder(['foo' => 'bar']);
        $builder->setEnv('baz');
        $builder->setDebug(true);

        $container = $builder->build();

        $this->assertInstanceOf(Container::class, $container);
        $this->assertSame('bar', $container->get('foo'));
        $this->assertSame('baz', $container->getEnv());
        $this->assertTrue($container->isDebug());
    }
}
