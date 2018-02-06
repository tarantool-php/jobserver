<?php

namespace App\Tests\Unit\UseCase\Greet;

use App\UseCase\Greet\GreetHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface as Logger;

final class GreetHandlerTest extends TestCase
{
    public function testInvoke(): void
    {
        $logger = $this->createMock(Logger::class);
        $logger->expects(self::once())->method('info')
            ->with('Hello World');

        $handler = new GreetHandler(false);
        $handler('World', $logger);
    }

    public function testInvokeWithYell(): void
    {
        $logger = $this->createMock(Logger::class);
        $logger->expects(self::once())->method('info')
            ->with('HELLO WORLD');

        $handler = new GreetHandler(true);
        $handler('World', $logger);
    }
}
