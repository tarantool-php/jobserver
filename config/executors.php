<?php

declare(strict_types=1);

use App\JobQueue\ServiceExecutor;
use App\Kernel;
use Tarantool\JobQueue\Executor\ProcessExecutor;

$container = Kernel::fromEnv()->getContainer();

return [
    new ServiceExecutor($container),
    new ProcessExecutor(),
];
