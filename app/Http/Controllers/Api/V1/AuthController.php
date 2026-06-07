<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
class AuthController extends Controller
{
    public function login(LoginRequest $request) {
        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->expires_at && $user->expires_at->isPast()) {
            return response()->json(['message' => 'Account expired'], 403);
        }
        $token = $user->createToken('auth-token')->plainTextToken;
        return response()->json(['token' => $token]);
    }
    public function register(RegisterRequest $request) {
        $user = User::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);
        return response()->json(['message' => 'registered'],201);
    }
    public function refresh(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $token = $request->user()->createToken('auth-token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}