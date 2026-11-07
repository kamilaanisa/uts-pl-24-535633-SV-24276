<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
{
    $data = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    $user = User::where('email', $data['email'])->first();

    if (! $user) {
        Log::warning('Login gagal: akun tidak ditemukan', [
            'aksi'   => 'auth.login',
            'alasan' => 'akun_tidak_ditemukan',
        ]);

        return response()->json(['message' => 'Email atau password salah'], 401);
    }

    if (! Hash::check($data['password'], $user->password)) {
        Log::warning('Login gagal: password tidak cocok', [
            'aksi'    => 'auth.login',
            'user_id' => $user->id,
            'alasan'  => 'password_salah',
        ]);

        return response()->json(['message' => 'Email atau password salah'], 401);
    }

    Log::info('Login berhasil', [
        'aksi'    => 'auth.login',
        'user_id' => $user->id,
    ]);

    return response()->json([
        'message' => 'Login berhasil',
        'token'   => $user->createToken('api-token')->plainTextToken,
        'user'    => $user,
    ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}