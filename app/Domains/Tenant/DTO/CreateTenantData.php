<?php
namespace App\Domains\Tenant\DTO;

class CreateTenantData
{
    public function __construct(
        public string $name,
        public ?string $address,
        public ?string $phone,
        public string $admin_name,
        public string $admin_email,
        public string $admin_password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            admin_name: $data['admin_name'],
            admin_email: $data['admin_email'],
            admin_password: $data['admin_password'],
        );
    }
}
