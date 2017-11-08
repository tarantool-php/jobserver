<?php

namespace App;

use App\Di\Container;
use App\Di\ContainerBuilder;

function container(): Container
{
    $getSettings = require __DIR__.'/config/settings.php';
    $builder = new ContainerBuilder($getSettings(dirname(__DIR__)));

    $localConfigFile = __DIR__.'/config/local.php';
    if (file_exists($localConfigFile)) {
        $configureContainer = require $localConfigFile;
        $configureContainer($builder);
    }

    return $builder->build();
}
