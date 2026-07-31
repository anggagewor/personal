<?php

namespace Modules\Shared\Infrastructure\Controllers;

use Illuminate\Routing\Controller;
use ReflectionMethod;
use ReflectionNamedType;

abstract class BaseController extends Controller
{
    /**
     * Execute an action on the controller.
     *
     * Laravel passes route parameters as strings, but controller methods in
     * this codebase type-hint scalars (e.g. `int $outletId`). PHP will not
     * coerce those when the call originates from framework code, so we cast
     * them here based on the method signature.
     */
    public function callAction($method, $parameters)
    {
        $reflection = new ReflectionMethod($this, $method);
        $positional = array_values($parameters);
        $args = [];

        foreach ($reflection->getParameters() as $index => $param) {
            $name = $param->getName();

            // Route parameters may be keyed by name or by position depending
            // on how the framework spliced in resolved class dependencies.
            if (array_key_exists($name, $parameters)) {
                $value = $parameters[$name];
            } elseif (array_key_exists($index, $positional)) {
                $value = $positional[$index];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            } else {
                continue;
            }

            $args[] = $this->castValue($value, $param->getType());
        }

        return $this->{$method}(...$args);
    }

    /**
     * Cast a raw route parameter to the type declared on the method signature.
     */
    private function castValue(mixed $value, ?object $type): mixed
    {
        if (! $type instanceof ReflectionNamedType || ! is_string($value)) {
            return $value;
        }

        return match ($type->getName()) {
            'int' => is_numeric($value) ? (int) $value : $value,
            'float' => is_numeric($value) ? (float) $value : $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $value,
            default => $value,
        };
    }
}
