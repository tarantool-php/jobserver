<?php

namespace App\UseCase\Greet;

use App\Di\Container;
use App\Di\Options;

class GreetHandlerFactory
{
    public function __invoke(Container $container): GreetHandler
    {
        return new GreetHandler(
            $container->get(Options::GREET_YELL)
        );
    }
}
