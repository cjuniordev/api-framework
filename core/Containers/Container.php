<?php

namespace Core\Containers;

use Exception;
use \ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Container
{
    public static Container $instance;
    public array $bindings = [];

    public static function getInstance(): Container
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function bind(string $key, $value): void
    {
        $this->bindings[$key] = $value;
    }

    /**
     * @throws Exception
     */
    public function get(string $key): mixed
    {
        if (!isset($this->bindings[$key])) {
            $this->bind($key, $key);
        }

        return $this->resolver($this->bindings[$key]);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    private function resolver(string $class): mixed
    {
        $reflected = new ReflectionClass($class);

        $constructor = $reflected->getConstructor();

        if (is_null($constructor)) {
            return new $class();
        }

        $parameters = $this->resolveParameters($constructor);

        if (count($parameters)) {
            return $reflected->newInstanceArgs($parameters);
        }

        return $this->get($class);
    }

    private function resolveParameters(ReflectionMethod $constructor): array
    {
        $parameters = $constructor->getParameters();

        try {
            $parameters = array_map(function ($parameter) {
                $type = $parameter->getType();
                $typeName = $type->getName();
                return $this->get($typeName);
            }, $parameters);
        } catch (ReflectionException|Exception $e) {
            echo $e->getMessage();
        }

        return $parameters;
    }
}