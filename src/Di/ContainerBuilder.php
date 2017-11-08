<?php

namespace App\Di;

class ContainerBuilder
{
    use Config;

    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->env = 'dev';
        $this->debug = false;
    }

    public function setEnv(string $env): self
    {
        $this->env = $env;

        return $this;
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function set(string $option, $value): self
    {
        $this->options[$option] = $value;

        return $this;
    }

    public function build(): Container
    {
        return new Container($this->options, $this->env, $this->debug);
    }
}
