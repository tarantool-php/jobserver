<?php

namespace App\Di;

final class Container
{
    use Config;
    use Handlers;
    use JobQueue;
    use Logging;

    public function __construct(array $options, string $env, bool $debug)
    {
        $this->setOptions($options);
        $this->setEnv($env);
        $this->setDebug($debug);
    }
}
