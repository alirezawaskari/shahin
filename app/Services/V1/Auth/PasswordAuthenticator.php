<?php
declare(strict_types=1);

namespace App\Services\V1\Auth;

use App\DTOs\Auth\PasswordCredentialsDTO;
use App\Exceptions\Auth\InactiveAccountException;
use App\Exceptions\Auth\InvalidAdminCredentialsException;
use App\Exceptions\Auth\NotAdminException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

final class PasswordAuthenticator
{

    public function authenticate(PasswordCredentialsDTO $credentials): User
    {
        $user = User::query()
            ->where('phone', $credentials->phone)
            ->first();



        if ($user === null || !Hash::check($credentials->password, $user->password)) {
            throw new InvalidAdminCredentialsException();
        }

        // if (!$user->isAdmin()) {
        //     throw new NotAdminException();
        // }

        // if (!$user->isActive()) {
        //     throw new InactiveAccountException();
        // }

        return $user;

    }

}
