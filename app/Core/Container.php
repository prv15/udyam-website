<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;
use Throwable;

class Container
{
    /**
     * Interface/Class bindings.
     *
     * @var array<string, mixed>
     */
    protected array $bindings = [];

    /**
     * Singleton instances.
     *
     * @var array<string, object>
     */
    protected array $instances = [];

    /**
     * Circular dependency detection.
     *
     * @var array<string, bool>
     */
    protected array $resolving = [];

    /**
     * Reflection cache.
     *
     * @var array<string, ReflectionClass>
     */
    protected array $reflections = [];

    /**
     * Bind a transient service.
     */
    public function bind(string $abstract, string|Closure $concrete): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared'   => false,
        ];
    }

    /**
     * Bind a singleton.
     */
    public function singleton(string $abstract, object|string $concrete): void
    {
        if (is_object($concrete) && !$concrete instanceof Closure) {
            $this->instances[$abstract] = $concrete;
            return;
        }

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared'   => true,
        ];
    }

    /**
     * Resolve a class.
     */
    public function get(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->resolving[$abstract])) {
            throw new RuntimeException(
                "Circular dependency detected while resolving {$abstract}"
            );
        }

        $this->resolving[$abstract] = true;

        try {

            $object = $this->resolve($abstract);

            if (
                isset($this->bindings[$abstract]) &&
                $this->bindings[$abstract]['shared']
            ) {
                $this->instances[$abstract] = $object;
            }

            unset($this->resolving[$abstract]);

            return $object;

        } catch (Throwable $e) {

            unset($this->resolving[$abstract]);

            throw $e;
        }
    }

    /**
     * Resolve object.
     */
    protected function resolve(string $abstract): object
    {
        if (isset($this->bindings[$abstract])) {

            $binding = $this->bindings[$abstract]['concrete'];

            if ($binding instanceof Closure) {
                return $binding($this);
            }

            if (is_string($binding)) {
                $abstract = $binding;
            }
        }

        $reflection = $this->reflection($abstract);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException(
                "Class {$abstract} is not instantiable."
            );
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $dependencies[] = $this->resolveParameter($parameter);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Resolve constructor parameter.
     */
    protected function resolveParameter(
        ReflectionParameter $parameter
    ): mixed {

        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType) {

            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new RuntimeException(
                "Unable to resolve parameter \${$parameter->getName()}."
            );
        }

        if ($type->isBuiltin()) {

            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new RuntimeException(
                "Cannot auto resolve scalar parameter \${$parameter->getName()}."
            );
        }

        return $this->get($type->getName());
    }

    /**
     * Reflection cache.
     */
    protected function reflection(string $class): ReflectionClass
    {
        if (!isset($this->reflections[$class])) {
            $this->reflections[$class] = new ReflectionClass($class);
        }

        return $this->reflections[$class];
    }

    /**
     * Check if binding exists.
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract])
            || isset($this->instances[$abstract])
            || class_exists($abstract);
    }

    /**
     * Remove singleton instance.
     */
    public function forget(string $abstract): void
    {
        unset($this->instances[$abstract]);
    }

    /**
     * Flush the container.
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->resolving = [];
        $this->reflections = [];
    }
}