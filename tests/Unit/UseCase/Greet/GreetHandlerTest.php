<?php

namespace App\Tests\Unit\Handler;

use App\UseCase\Greet\GreetHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface as Logger;

class GreetHandlerTest extends TestCase
{
    public function testInvoke(): void
    {
        $logger = $this->createMock(Logger::class);
        $logger->expects($this->once())->method('info')
            ->with('Hello World');

        $handler = new GreetHandler(false);
        $handler('World', $logger);
    }

    public function testInvokeWithYell(): void
    {
        $logger = $this->createMock(Logger::class);
        $logger->expects($this->once())->method('info')
            ->with('HELLO WORLD');

        $handler = new GreetHandler(true);
        $handler('World', $logger);
    }
}
