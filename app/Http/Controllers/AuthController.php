<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function obtenerUsuarios() 
    {
        $usuarios = Usuario::all();

        return response()->json([
            'success' => true,
            'data' => $usuarios
        ], 200);
    }
}
