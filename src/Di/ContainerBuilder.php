<?php

namespace App\Di;

class ContainerBuilder
{
    use Config {
        set as public;
        setEnv as public;
        setDebug as public;
        setOptions as public;
    }

    public function __construct(array $options = [])
    {
        $this->setEnv('dev');
        $this->setDebug(false);
        $this->setOptions($options);
    }

    public function build(): Container
    {
        return new Container($this->options, $this->env, $this->debug);
    }
}
