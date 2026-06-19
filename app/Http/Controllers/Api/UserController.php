<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Muestra todos los usuarios.
     */
    public function index()
    {
        return UserCollection::make(
            User::all()
        );
    }

    /**
     * Crea un nuevo usuario.
     */
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:6',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
        ]);

        $user = User::create([
            'name' => $datosValidados['name'],

            'email' => $datosValidados['email']
                ?? null,

            'password' => Hash::make(
                $datosValidados['password']
            ),

            'phone' => $datosValidados['phone']
                ?? null,
        ]);

        return UserResource::make($user);
    }

    /**
     * Muestra un usuario específico.
     */
    public function show(User $user)
    {
        return UserResource::make($user);
    }

    /**
     * Actualiza un usuario.
     */
    public function update(
        Request $request,
        User $user
    ): JsonResponse {

        $datosValidados = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'sometimes',
                'nullable',
                'email',

                Rule::unique(
                    'users',
                    'email'
                )->ignore($user->id),
            ],

            'password' => [
                'sometimes',
                'nullable',
                'string',
                'min:6',
            ],

            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
            ],
        ]);

        $data = [];

        if (
            array_key_exists(
                'name',
                $datosValidados
            )
        ) {
            $data['name'] =
                $datosValidados['name'];
        }

        if (
            array_key_exists(
                'email',
                $datosValidados
            )
        ) {
            $data['email'] =
                $datosValidados['email'];
        }

        if (
            array_key_exists(
                'phone',
                $datosValidados
            )
        ) {
            $data['phone'] =
                $datosValidados['phone'];
        }

        if (
            !empty(
                $datosValidados['password']
                ?? null
            )
        ) {
            $data['password'] = Hash::make(
                $datosValidados['password']
            );
        }

        $user->update($data);

        $user->refresh();

        return response()->json([
            'message' =>
                'User updated successfully',

            'data' =>
                UserResource::make($user),
        ], 200);
    }

    /**
     * Elimina un usuario.
     */
    public function destroy(
        User $user
    ): JsonResponse {

        $userName = $user->name;

        $user->delete();

        return response()->json([
            'message' =>
                "User '{$userName}' deleted successfully",
        ], 200);
    }
}