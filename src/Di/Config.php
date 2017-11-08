<?php

namespace App\Di;

trait Config
{
    private $options;
    private $env;
    private $debug;

    public function getEnv(): string
    {
        return $this->env;
    }

    public function isDebug(): bool
    {
        return $this->debug;
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
