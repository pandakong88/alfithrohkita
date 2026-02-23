<?php

namespace App\Domains\Role\DTO;

class CreateRoleData
{
    public function __construct(
        public string $name,
        public array $permissions
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            permissions: $data['permissions'] ?? []
        );
    }
}
