<?php

namespace App\Di;

use App\UseCase\Greet\GreetHandler;
use App\UseCase\Greet\GreetHandlerFactory;

trait Handlers
{
    public function getGreetHandler(): GreetHandler
    {
        static $handler;

        return $handler ?? $handler = (new GreetHandlerFactory())($this);
    }
}
