<?php

use App\Di\Options;

return function (string $rootDir): array {
    return [
        Options::GREET_YELL => false,
        Options::LOGGER_FILE => "$rootDir/var/log/workers.log",
    ];
};
