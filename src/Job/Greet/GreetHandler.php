<?php

declare(strict_types=1);

namespace App\Job\Greet;

use Psr\Log\LoggerInterface;

final class GreetHandler
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(string $name, TextDecorator $decorator = null, bool $yell = false) : void
    {
        $text = "Hello $name";

        if ($yell) {
            $text = \strtoupper($text);
        }

        if ($decorator) {
            $text = $decorator->decorate($text);
        }

        $this->logger->info($text);
    }
}
