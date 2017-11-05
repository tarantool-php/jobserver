<?php

namespace App\Di;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;

trait Logging
{
    public function getLogger(): LoggerInterface
    {
        static $logger;

        return $logger ?? $logger = new MonologLogger('worker', [
            new StreamHandler($this->get(Options::LOGGER_FILE)),
        ]);
    }

    abstract public function get(string $option);
}
