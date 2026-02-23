<?php

namespace App\Domains\User\DTO;

class CreateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public int $role_id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role_id: $data['role_id'],
        );
    }
}
