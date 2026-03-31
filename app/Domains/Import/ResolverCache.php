<?php

namespace App\Domains\Import;

class ResolverCache
{
    protected $items = [];

    public function get($entity, $key)
    {
        return $this->items[$entity][$key] ?? null;
    }

    public function set($entity, $key, $model)
    {
        $this->items[$entity][$key] = $model;
    }
}

