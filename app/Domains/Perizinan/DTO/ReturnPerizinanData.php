<?php

namespace App\Domains\Perizinan\DTO;

class ReturnPerizinanData
{
    public function __construct(
        public int $perizinan_id
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            perizinan_id: $data['perizinan_id']
        );
    }
}