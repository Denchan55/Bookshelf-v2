<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthTokenController extends Controller
{
    public function store(Request $request)
    {
        //dd(Auth::user());

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => '認証に失敗しました。',
            ], 401);
        }

        $user = Auth::user();

        // Sanctum の Personal Access Token を発行
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
        ]);
    }
}
