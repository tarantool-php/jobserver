<?php

$rootDir = dirname(__DIR__, 2);

foreach ([
    '/app/config/jobserver_config.lua',
    '/app/config/local.php',
    '/.env',
    '/docker-compose.override.yml',
] as $toFile) {
    if (file_exists($rootDir.$toFile)) {
        continue;
    }
    if (!@copy($rootDir.$toFile.'.dist', $rootDir.$toFile)) {
        echo "Failed to copy $toFile.dist file.\n";
        exit(42);
    }
}
