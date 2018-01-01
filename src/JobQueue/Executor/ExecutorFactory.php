<?php

namespace App\JobQueue\Executor;

use App\Di\Container;
use App\JobQueue\Executor\CallbackResolver\ContainerCallbackResolver;
use Tarantool\JobQueue\Executor\CallbackExecutor;
use Tarantool\JobQueue\Executor\Executor;
use Tarantool\JobQueue\Executor\ExecutorChain;
use Tarantool\JobQueue\Executor\ProcessExecutor;

class ExecutorFactory
{
    public function __invoke(Container $container): Executor
    {
        $callbackExecutor = new CallbackExecutor(
            new ContainerCallbackResolver($container),
            $container->getJobQueueAutowiredArgs()
        );

        return new ExecutorChain([
            $callbackExecutor,
            new ProcessExecutor(),
        ]);
    }
}
