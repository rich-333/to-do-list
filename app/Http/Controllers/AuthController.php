<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function obtenerUsuarios() 
    {
        $usuarios = Usuario::all();

        return new JsonResponse([
            'success' => true,
            'data' => $usuarios
        ], 200);
    }
}
