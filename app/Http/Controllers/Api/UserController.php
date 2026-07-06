<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
                'nullable',
                'string',
                'min:6',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'image' => [
                'nullable',
                'image',
                'max:2048',
            ],
        ]);

        $user = User::create([
            'name' => $datosValidados['name'],

            'email' => $datosValidados['email']
                ?? null,

            'password' => Hash::make(
                $datosValidados['password'] ?? '12345678'
            ),

            'phone' => $datosValidados['phone']
                ?? null,
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('users', 'public');
            $user->image()->create([
                'url' => asset(Storage::url($path)),
            ]);
        }

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

            'image' => [
                'nullable',
                'image',
                'max:2048',
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

        if ($request->hasFile('image')) {
            if ($user->image) {
                $oldPath = str_replace(asset('storage/'), '', $user->image->url);
                Storage::disk('public')->delete($oldPath);
                $user->image->delete();
            }

            $path = $request->file('image')->store('users', 'public');
            $user->image()->create([
                'url' => asset(Storage::url($path)),
            ]);
        }

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

        if ($user->image) {
            $oldPath = str_replace(asset('storage/'), '', $user->image->url);
            Storage::disk('public')->delete($oldPath);
            $user->image->delete();
        }

        $user->delete();

        return response()->json([
            'message' =>
                "User '{$userName}' deleted successfully",
        ], 200);
    }
}