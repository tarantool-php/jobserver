<?php

namespace App\Di;

use App\JobQueue\Executor\ExecutorFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Tarantool\JobQueue\DefaultConfigFactory;
use Tarantool\JobQueue\Executor\Executor;

trait JobQueue
{
    public function getJobQueueConfigFactory(): DefaultConfigFactory
    {
        static $factory;

        return $factory ?? $factory = (new DefaultConfigFactory())
            ->setLogFile($this->get(Options::LOGGER_FILE))
            ->setLogLevel($this->isDebug() ? LogLevel::DEBUG : LogLevel::INFO)
            ->setConnectionOptions(['tcp_nodelay' => true])
        ;
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

    abstract public function getLogger(): LoggerInterface;

    abstract public function isDebug(): bool;
}
