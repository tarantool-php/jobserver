<?php

namespace App\JobQueue\Executor\CallbackResolver;

use App\Di\Container;
use Tarantool\JobQueue\Exception\BadPayloadException;
use Tarantool\JobQueue\Executor\CallbackResolver\CallbackResolver;
use Tarantool\JobQueue\JobOptions;

class ContainerCallbackResolver implements CallbackResolver
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function resolve($payload): callable
    {
        if (empty($payload[JobOptions::PAYLOAD_SERVICE])) {
            throw BadPayloadException::missingOrEmptyKeyValue($payload, JobOptions::PAYLOAD_SERVICE, 'string', __CLASS__);
        }

        $handlerName = $payload[JobOptions::PAYLOAD_SERVICE];
        $getter = 'get'.self::camelize($handlerName).'Handler';

        if (method_exists($this->container, $getter)) {
            return $this->container->$getter();
        }

        throw new \InvalidArgumentException(sprintf('Unknown handler "%s".', $handlerName));
    }

    private static function camelize(string $value): string
    {
        return strtr(ucwords(strtr($value, ['_' => ' ', '.' => '_ ', '\\' => '_ '])), [' ' => '']);
    }
}
