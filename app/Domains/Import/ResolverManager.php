<?php

namespace App\Domains\Import;

class ResolverManager
{
    protected $cache = [];

    public function get($entity)
    {
        if (isset($this->cache[$entity])) {
            return $this->cache[$entity];
        }

        $class = "App\\Domains\\Import\\Resolvers\\" . ucfirst($entity) . "Resolver";

        if (!class_exists($class)) {
            return null;
        }

        $this->cache[$entity] = new $class();

        return $this->cache[$entity];
    }
}
