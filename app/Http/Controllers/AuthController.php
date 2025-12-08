<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function obtenerUsuarios()
    {
        try {
            $usuarios = User::all();

            return new JsonResponse([
                'success' => true,
                'data' => $usuarios
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error obtenerUsuarios: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
}
