<?php

declare(strict_types=1);

namespace App\Tests\Unit\Job\Greet;

use App\Job\Greet\GreetHandler;
use App\Job\Greet\TextDecorator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class GreetHandlerTest extends TestCase
{
    public function testInvoke() : void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')
            ->with('**Hello World**');

        $handler = new GreetHandler($logger);
        $handler('World', new TextDecorator());
    }

    public function testInvokeWithYell() : void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')
            ->with('**HELLO WORLD**');

        $handler = new GreetHandler($logger);
        $handler('World', new TextDecorator(), true);
    }
}
