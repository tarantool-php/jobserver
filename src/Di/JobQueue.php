<?php

namespace App\Di;

use App\JobQueue\Executor\ExecutorFactory;
use Psr\Log\LoggerInterface as Logger;
use Tarantool\JobQueue\DefaultConfigFactory;
use Tarantool\JobQueue\Executor\Executor;

trait JobQueue
{
    public function getJobQueueConfigFactory(): DefaultConfigFactory
    {
        static $factory;

        if (!$factory) {
            $factory = new DefaultConfigFactory();
            $factory->setConnectionOptions(['tcp_nodelay' => true]);
        }

        return $factory;
    }

    public function getJobQueueExecutor(): Executor
    {
        static $executor;

        return $executor ?? $executor = (new ExecutorFactory())($this);
    }

    public function getJobQueueAutowiredArgs(): array
    {
        return [
            'logger' => $this->getLogger(),
            'container' => $this,
        ];
    }

    abstract public function get(string $option);

    abstract public function getLogger(): Logger;
}
