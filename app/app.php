<?php

namespace App;

use App\Di\Container;

function container(): Container
{
    $getSettings = require __DIR__.'/config/settings.php';
    $container = new Container($getSettings(dirname(__DIR__)));

    $container->setEnv(getenv('APP_ENV') ?: 'prod');
    $container->setDebug(getenv('APP_DEBUG') ?: false);

    $localConfigFile = __DIR__.'/config/local.php';
    if (file_exists($localConfigFile)) {
        $extendContainer = require $localConfigFile;
        $container = $extendContainer($container);
    }

    return $container;
}
