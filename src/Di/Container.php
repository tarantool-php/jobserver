<?php

namespace App\Di;

class Container
{
    use Handlers;
    use JobQueue;
    use Logging;

    private $env = 'dev';
    private $debug = false;
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function setEnv(string $env): self
    {
        $this->env = $env;

        return $this;
    }

    public function getEnv(): string
    {
        return $this->env;
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function set(string $option, $value): self
    {
        $this->options[$option] = $value;

        return $this;
    }

    public function get(string $option)
    {
        if (!array_key_exists($option, $this->options)) {
            throw new \InvalidArgumentException(sprintf('Unknown option "%s".', $option));
        }

        return $this->options[$option];
    }

    public function tryGet(string $option, $default = null)
    {
        return array_key_exists($option, $this->options)
            ? $this->options[$option]
            : $default;
    }
}
