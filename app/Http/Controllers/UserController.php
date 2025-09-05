<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('limit', 10);
        $page = $request->get('page', 1);

        $users = Usuario::select('id', 'nome', 'email', 'created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($users);
    }

    public function show($id)
    {
        $user = Usuario::select('id', 'nome', 'email', 'created_at')->find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        return response()->json($user);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'senha' => 'required|min:6'
        ]);

        $user = Usuario::create($request->only(['nome', 'email', 'senha']));

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'user' => $user->only(['id', 'nome', 'email', 'created_at'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = Usuario::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $request->validate([
            'nome' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:usuarios,email,' . $id,
            'senha' => 'sometimes|min:6'
        ]);

        $user->update($request->only(['nome', 'email', 'senha']));

        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'user' => $user->only(['id', 'nome', 'email', 'updated_at'])
        ]);
    }

    public function destroy($id)
    {
        $user = Usuario::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Usuário excluído com sucesso']);
    }
}