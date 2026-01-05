<?php

declare(strict_types=1);

namespace App\Domain\User\Actions;

use App\Domain\User\DTOs\LoginData;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class AuthenticateUser
{
    /**
     * Execute the action to authenticate a user.
     *
     * @throws ValidationException
     */
    public function execute(LoginData $data): User
    {
        if (! Auth::attempt(['email' => $data->email, 'password' => $data->password], $data->remember)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            throw new RuntimeException('Authenticated user is not an instance of User model.');
        }

        return $user;
    }

    /**
     * Execute the action and create an API token.
     *
     * @throws ValidationException
     */
    public function executeWithToken(LoginData $data, string $tokenName = 'api-token'): array
    {
        $user = User::query()->where('email', $data->email)->first();

        if (! $user || ! \Illuminate\Support\Facades\Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
