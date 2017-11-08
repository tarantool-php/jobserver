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
        $this->options = $options;
        $this->env = $env;
        $this->debug = $debug;
    }
}
