<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use DB;

class UserController extends Controller
{
    public function index(){
        $users = User::with('shop', 'typeUser', 'branch')->get();
        return response()->json(["users" => $users]);
    }

    public function store(Request $request)
    {
        // Opcional: Usar una política de autorización para verificar el rol del usuario
        // $this->authorize('create', User::class);

        // Validar que el usuario autenticado sea un admin (type_user_id = 2)
        // return $request->user();
        if ($request->user()->type_user_id !== 1) {
            return response()->json(['message' => 'Acceso denegado. Solo los administradores pueden crear nuevos usuarios.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type_user_id' => 3, // Tipo de usuario vendedor
                'shop_id' => $request->user()->shop_id,
                'branch_id' => $request->branch_id,
            ]);

            DB::commit();

            return response()->json(['message' => 'Usuario vendedor creado correctamente.', 'user' => $user], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el usuario.', 'error' => $e->getMessage()], 500);
        }
    }

    public function disableUser(Request $request, $id)
    {
        $admin = Auth::user();

        // Solo tipo 1 (admin) puede deshabilitar
        if ($admin->type_user_id !== 1) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción.'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado.'
            ], 404);
        }

        if ($user->deleted_at !== null) {
            return response()->json([
                'message' => 'El usuario ya se encuentra deshabilitado.'
            ], 400);
        }

        $user->deleted_at = Carbon::now('America/Mexico_City');
        $user->save();

        return response()->json([
            'message' => 'Usuario deshabilitado correctamente.',
            'user' => $user
        ]);
    }

}