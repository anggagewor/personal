<?php

namespace Modules\User\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\User\Application\Actions\RegisterUserAction;
use Modules\User\Application\DTO\UserData;
use Modules\User\Infrastructure\Models\UserModel;
use Modules\User\Infrastructure\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = UserModel::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Revoke existing tokens to prevent token accumulation
        $user->tokens()->delete();

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'preferences' => $user->getPreferencesWithDefaults(),
                ],
                'token' => $token,
                'refresh_token' => $user->createToken('refresh', ['refresh'])->plainTextToken,
            ],
        ]);
    }

    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $user = $action->execute(UserData::fromArray($request->validated()));

        $model = UserModel::find($user->id);
        $token = $model->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'preferences' => $user->getPreferencesWithDefaults(),
                ],
                'token' => $token,
                'refresh_token' => $model->createToken('refresh', ['refresh'])->plainTextToken,
            ],
        ], 201);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'data' => [
                'token' => $user->createToken('auth')->plainTextToken,
                'refresh_token' => $user->createToken('refresh', ['refresh'])->plainTextToken,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'preferences' => $user->getPreferencesWithDefaults(),
            ],
        ]);
    }
}
