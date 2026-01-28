<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole('user');
        $user->sendEmailVerificationNotification();
        return response()->json([
            'message' => 'Usuario registrado. Revisa tu email para verificar.',
            'user' => $user
        ], 201);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $login = $request->login;
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (!Auth::attempt([$field => $login, 'password' => $request->password])) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }
        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email no verificado'], 403);
        }
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }
    
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user || !hash_equals($hash, sha1($user->email))) {
            return response()->json(['message' => 'Enlace inválido'], 400);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email ya verificado']);
        }

        User::where('id', $id)->update(['email_verified_at' => now()]);

        event(new Verified($user));

        return response()->json(['message' => 'Email verificado correctamente']);
    }
}
