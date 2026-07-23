<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class AuthService extends Service
{
    private ?User $user = null;

    public function findUser(string $email): ?array
    {
        return ($this->user ??= new User())->findByEmail($email);
    }
}
