<?php

declare(strict_types=1);
namespace App\DTOs\Auth;

final readonly class PasswordCredentialsDTO
{
    public function __construct(
        public  string $phone,
        public  string $password,
    ) {
    }

}
