<?php

namespace App\Di;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

trait Logging
{
    public function getLogger(): LoggerInterface
    {
        static $logger;

        return $logger ?? $logger = new Logger('jobserver', [
            new StreamHandler(
                $this->get(Options::LOGGER_FILE),
                $this->isDebug() ? Logger::DEBUG : Logger::INFO
            )
        ]);
    }

    abstract public function get(string $option);

    abstract public function isDebug(): bool;
}
