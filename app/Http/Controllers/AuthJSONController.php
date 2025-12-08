<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthJSONController extends Controller
{
    /**
     * Registrar un nuevo usuario
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Log in and remember so the session persists across reloads
            Auth::login($user, true);

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'user' => $user
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();

            // Mapear mensajes comunes a mensajes más amigables en español
            $friendlyMessage = 'Error de validación';
            if (isset($errors['email'])) {
                foreach ($errors['email'] as $msg) {
                    if (stripos($msg, 'unique') !== false || stripos($msg, 'ya ha sido tomado') !== false || stripos($msg, 'already been taken') !== false) {
                        $friendlyMessage = 'El correo electrónico ya está registrado. Si es tu cuenta, inicia sesión.';
                        break;
                    }
                }
            }

            return response()->json([
                'message' => $friendlyMessage,
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Iniciar sesión
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // Use remember token so session persists across browser reloads/long periods
            $remember = true;
            if (Auth::attempt($credentials, $remember)) {
                $request->session()->regenerate();
                return response()->json([
                    'message' => 'Sesión iniciada correctamente',
                    'user' => Auth::user()
                ], 200);
            }

            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al iniciar sesión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ], 200);
    }
}
