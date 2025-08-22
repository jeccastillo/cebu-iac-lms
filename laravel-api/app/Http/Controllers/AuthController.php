<?php

namespace App\Http\Controllers;

use App\Models\LegacyUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Simple health check to validate app and DB connectivity.
     */
    public function health(): JsonResponse
    {
        try {
            DB::select('SELECT 1');
            return response()->json(['status' => 'ok'], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'degraded',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Token-based login for API clients using Sanctum personal access tokens.
     * Input: { username: string, password: string }
     * Output: { token: string, user: {...} }
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        /** @var LegacyUser|null $user */
        $user = LegacyUser::where('strUsername', $data['username'])->first();

        if (!$user || !$user->checkPassword($data['password'])) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // IMPORTANT: Do NOT rehash to bcrypt here yet to avoid breaking the legacy app.
        // Rehash-on-login should be deferred until full cutover or a dual-field strategy exists.

        // Create a personal access token
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->getKey(),
                'username' => $user->strUsername,
                'email' => $user->strEmail ?? null,
                'program_id' => $user->intProgramID ?? null,
                'rog' => $user->intROG ?? null,
            ],
        ], 200);
    }

    /**
     * Revoke current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logged out',
        ], 200);
    }

    /**
     * Return current authenticated user (requires auth:sanctum).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'id' => $user->getKey(),
            'username' => $user->strUsername ?? null,
            'email' => $user->strEmail ?? null,
            'program_id' => $user->intProgramID ?? null,
            'rog' => $user->intROG ?? null,
        ], 200);
    }
}
