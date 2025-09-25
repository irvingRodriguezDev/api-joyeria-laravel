<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registro de usuario
     */
    public function register(Request $request)
    {
        $request->validate([
            'name_user' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'name_shop' => 'required|string|max:255',
            'state' => 'required|string',
            'municipality' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name_user,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->shop()->create([
                'name' => $request->name_shop,
                'state' => $request->state,
                'municipality' => $request->municipality,
            ]);

            DB::commit();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['user' => $user, 'token' => $token], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar el usuario y la tienda.', 'error' => $e->getMessage()], 500);
        }

    }

    /**
     * Login de usuario
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout - Revocar token actual
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    /**
     * Retornar usuario autenticado
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}