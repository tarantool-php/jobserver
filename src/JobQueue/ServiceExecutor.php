<?php

declare(strict_types=1);

namespace App\JobQueue;

use ArgumentsResolver\InDepthArgumentsResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tarantool\JobQueue\Executor\CallbackResolver\Psr11ContainerCallbackResolver;
use Tarantool\JobQueue\Executor\Executor;
use Tarantool\JobQueue\JobBuilder\JobOptions;
use Tarantool\Queue\Queue;

final class ServiceExecutor implements Executor
{
    private $container;
    private $boundArguments;

    public function __construct(ContainerInterface $container, array $boundArguments = [])
    {
        $this->container = $container;
        $this->boundArguments = $boundArguments;
    }

    public function execute($payload, Queue $queue) : void
    {
        $resolver = new Psr11ContainerCallbackResolver($this->container);
        $callback = $resolver->resolve($payload);

        $args = $payload[JobOptions::PAYLOAD_SERVICE_ARGS] ?? [];

        $args = \array_merge($args, [
            $payload,
            $queue,
        ], $this->boundArguments);

        $args = $this->interpolateArguments($args);

        $args = (new InDepthArgumentsResolver($callback))->resolve($args);

        $callback(...$args);
    }

    private function interpolateArguments(array $args) : array
    {
        foreach ($args as $key => $arg) {
            if (!\is_string($arg) || '' === $arg) {
                continue;
            }

            if ('@' === $arg[0] && $this->container->has($id = \substr($arg, 1))) {
                $args[$key] = $this->container->get($id);
                continue;
            }

            if ('%' === $arg[0] && '%' === $arg[-1] && $this->container->hasParameter($name = \substr($arg, 1, -1))) {
                $args[$key] = $this->container->getParameter($name);
            }
        }

        return $args;
    }
}
