<?php

namespace App;

use App\Di\Container;
use App\UseCase\Greet\GreetCommand;

return function (Container $container): array {
    return [
        new GreetCommand($container->getGreetHandler()),
    ];
};
