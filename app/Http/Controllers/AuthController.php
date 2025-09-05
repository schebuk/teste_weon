<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\AuthToken;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'senha' => 'required'
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !password_verify($request->senha, $usuario->senha)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        $token = AuthToken::generateToken($usuario->id);

        return response()->json([
            'token' => $token->token,
            'expires_at' => $token->expires_at
        ]);
    }
}