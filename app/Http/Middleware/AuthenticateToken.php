<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuthToken;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token de autenticação não fornecido'], 401);
        }

        $authToken = AuthToken::where('token', $token)->first();

        if (!$authToken || !$authToken->isValid()) {
            return response()->json(['error' => 'Token inválido ou expirado'], 401);
        }

        $request->merge(['auth_user' => $authToken->usuario]);

        return $next($request);
    }
}