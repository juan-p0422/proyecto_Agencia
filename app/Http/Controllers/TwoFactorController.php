<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'Correo' => ['required', 'email'],
            'Password' => ['required', 'string'],
        ]);

        // Rate limit básico por correo+IP (anti fuerza bruta)
        $key = 'login:' . strtolower($data['Correo']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json(['message' => 'Demasiados intentos, espera un momento'], 429);
        }
        RateLimiter::hit($key, 60);

        $user = Usuario::where('Correo', $data['Correo'])->first();

        if (!$user || !Hash::check($data['Password'], $user->Password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $twoFactorEnabled = !empty($user->two_factor_secret) && !empty($user->two_factor_confirmed_at);

        // 1) Si ya tiene 2FA → pedir OTP (no entregar token final)
        if ($twoFactorEnabled) {
            $pendingToken = Str::random(64);

            Cache::put("2fa_pending:{$pendingToken}", [
                'user_id' => $user->IdUsuario,
            ], now()->addMinutes(5));

            return response()->json([
                'two_factor_required' => true,
                'enroll_required' => false,
                'pending_token' => $pendingToken,
                'user_hint' => ['Correo' => $user->Correo],
            ]);
        }

        // 2) Si NO tiene 2FA → devolver QR automáticamente (no entregar token final)
        return $this->startEnrollmentForUser($user);
    }

    public function login2fa(Request $request)
    {
        $data = $request->validate([
            'pending_token' => ['required', 'string'],
            'code' => ['required', 'digits:6'],
        ]);

        $payload = Cache::get("2fa_pending:{$data['pending_token']}");
        if (!$payload) {
            return response()->json(['message' => 'Login pendiente expirado'], 401);
        }

        $user = Usuario::findOrFail($payload['user_id']);

        if (empty($user->two_factor_secret) || empty($user->two_factor_confirmed_at)) {
            return response()->json(['message' => '2FA no configurado'], 409);
        }

        $secret = Crypt::decryptString($user->two_factor_secret);
        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($secret, $data['code'], 1)) {
            return response()->json(['message' => 'Código inválido'], 422);
        }

        Cache::forget("2fa_pending:{$data['pending_token']}");

        // 🔹 MAGIA JWT: Generamos el token
        $token = auth('api')->login($user);

        return response()->json([
            'token' => $token,
            'usuario' => $user->makeHidden(['Password', 'two_factor_secret', 'two_factor_recovery_codes']),
        ]);
    }

    // ========== (Protegidos: auth:api) ==========
    public function setup(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $google2fa = new Google2FA();

        if (!$user->two_factor_secret) {
            $secret = $google2fa->generateSecretKey();
            $user->two_factor_secret = Crypt::encryptString($secret);
            $user->two_factor_confirmed_at = null;
            $user->save();
        } else {
            $secret = Crypt::decryptString($user->two_factor_secret);
        }

        $appName = config('app.name', 'Agencia');
        $otpauthUrl = $google2fa->getQRCodeUrl($appName, $user->Correo, $secret);

        return response()->json([
            'otpauthUrl' => $otpauthUrl,
            'manualKey' => $secret, // en producción evita mandarlo
            'confirmed' => !is_null($user->two_factor_confirmed_at),
        ]);
    }

    public function confirm(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if (!$user->two_factor_secret) {
            return response()->json(['message' => 'Primero genera el setup'], 400);
        }

        $secret = Crypt::decryptString($user->two_factor_secret);
        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($secret, $data['code'], 1)) {
            return response()->json(['message' => 'Código inválido'], 422);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        return response()->json(['message' => '2FA activado']);
    }

    public function disable(Request $request)
    {
        $user = $request->user();

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return response()->json(['message' => '2FA desactivado']);
    }

    // ========== (Sin sesión) ==========
    public function enrollStart(Request $request)
    {
        $data = $request->validate([
            'Correo' => ['required', 'email'],
            'Password' => ['required', 'string'],
        ]);

        $key = '2fa-enroll:' . strtolower($data['Correo']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Demasiados intentos, espera un momento'], 429);
        }
        RateLimiter::hit($key, 60);

        $user = Usuario::where('Correo', $data['Correo'])->first();

        if (!$user || !Hash::check($data['Password'], $user->Password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $twoFactorEnabled = !empty($user->two_factor_secret) && !empty($user->two_factor_confirmed_at);
        if ($twoFactorEnabled) {
            return response()->json(['message' => '2FA ya está activado'], 409);
        }

        return $this->startEnrollmentForUser($user);
    }

    public function enrollConfirm(Request $request)
    {
        $data = $request->validate([
            'enroll_token' => ['required', 'string'],
            'code' => ['required', 'digits:6'],
        ]);

        $payload = Cache::get("2fa_enroll:{$data['enroll_token']}");
        if (!$payload) {
            return response()->json(['message' => 'Enrolamiento expirado'], 401);
        }

        $google2fa = new Google2FA();
        $secret = $payload['secret'];

        if (!$google2fa->verifyKey($secret, $data['code'], 1)) {
            return response()->json(['message' => 'Código inválido'], 422);
        }

        $user = Usuario::findOrFail($payload['user_id']);

        $user->two_factor_secret = Crypt::encryptString($secret);
        $user->two_factor_confirmed_at = now();
        $user->save();

        Cache::forget("2fa_enroll:{$data['enroll_token']}");

        // 🔹 MAGIA JWT: Generamos el token
        $token = auth('api')->login($user);

        return response()->json([
            'message' => '2FA activado',
            'token' => $token,
            'usuario' => $user->makeHidden(['Password', 'two_factor_secret', 'two_factor_recovery_codes']),
        ]);
    }

    // ========== Helpers ==========
    private function startEnrollmentForUser(Usuario $user)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $enrollToken = Str::random(64);

        Cache::put("2fa_enroll:{$enrollToken}", [
            'user_id' => $user->IdUsuario,
            'secret' => $secret,
        ], now()->addMinutes(10));

        $appName = config('app.name', 'Agencia');
        $otpauthUrl = $google2fa->getQRCodeUrl($appName, $user->Correo, $secret);

        return response()->json([
            'two_factor_required' => false,
            'enroll_required' => true,
            'enroll_token' => $enrollToken,
            'otpauthUrl' => $otpauthUrl,
            // 'manualKey' => $secret, // evita mandarlo en producción
        ]);
    }
}
