<?php

declare(strict_types=1);

use App\Kernel;
use Psr\Log\LogLevel;
use Tarantool\JobQueue\DefaultConfigFactory;

$kernel = Kernel::fromEnv();
$container = $kernel->getContainer();

return $container->get(DefaultConfigFactory::class)
    ->setLogFile($container->getParameter('app.logger.file_path'))
    ->setLogLevel($kernel->isDebug() ? LogLevel::DEBUG : LogLevel::INFO);
