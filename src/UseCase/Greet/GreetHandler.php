<?php

namespace App\UseCase\Greet;

use Psr\Log\LoggerInterface as Logger;

class GreetHandler
{
    private $yell;

    public function __construct(bool $yell)
    {
        $this->yell = $yell;
    }

    public function __invoke(string $name, Logger $logger): void
    {
        $text = "Hello $name";

        if ($this->yell) {
            $text = strtoupper($text);
        }

        $logger->info($text);
    }
}
