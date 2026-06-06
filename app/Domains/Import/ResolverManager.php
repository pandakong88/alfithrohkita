<?php

namespace App\Domains\Import;

use Illuminate\Support\Str;

class ResolverManager
{
    protected $cache = [];

    public function get($entity)
    {
        if (isset($this->cache[$entity])) {
            return $this->cache[$entity];
        }

        // Gunakan Str::studly agar 'lemari_slot' -> 'LemariSlot'
        $className = Str::studly($entity) . "Resolver";
        $class = "App\\Domains\\Import\\Resolvers\\" . $className;

        if (!class_exists($class)) {
            return null;
        }

        $this->cache[$entity] = new $class();

        return $this->cache[$entity];
    }
}