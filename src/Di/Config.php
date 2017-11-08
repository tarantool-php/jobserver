<?php

namespace App\Di;

trait Config
{
    private $env;
    private $debug;
    private $options;

    private function setEnv(string $env): self
    {
        $this->env = $env;

        return $this;
    }

    public function getEnv(): string
    {
        return $this->env;
    }

    private function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    private function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    private function set(string $option, $value): self
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
