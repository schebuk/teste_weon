<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Services\CurrencyService;

class OrderController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('limit', 10);
        $page = $request->get('page', 1);

        $orders = Pedido::with('usuario:id,nome,email')
            ->paginate($perPage, ['*'], 'page', $page);

        $orders->getCollection()->transform(function ($order) {
            return $this->currencyService->getOrderWithConversions($order);
        });

        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Pedido::with('usuario:id,nome,email')->find($id);

        if (!$order) {
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        $order = $this->currencyService->getOrderWithConversions($order);

        return response()->json($order);
    }

    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:500',
            'valor' => 'required|numeric|min:0',
            'moeda' => 'required|in:BRL,USD'
        ]);

        $orderData = $request->only(['descricao', 'valor', 'moeda']);
        $orderData['usuario_id'] = $request->auth_user->id;

        $order = Pedido::create($orderData);
        $order = $this->currencyService->getOrderWithConversions($order);

        return response()->json([
            'message' => 'Pedido criado com sucesso',
            'order' => $order
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $order = Pedido::find($id);

        if (!$order) {
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        if ($order->usuario_id !== $request->auth_user->id) {
            return response()->json(['error' => 'Acesso não autorizado'], 403);
        }

        $request->validate([
            'descricao' => 'sometimes|string|max:500',
            'valor' => 'sometimes|numeric|min:0',
            'moeda' => 'sometimes|in:BRL,USD'
        ]);

        $order->update($request->only(['descricao', 'valor', 'moeda']));
        $order = $this->currencyService->getOrderWithConversions($order);

        return response()->json([
            'message' => 'Pedido atualizado com sucesso',
            'order' => $order
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $order = Pedido::find($id);

        if (!$order) {
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        if ($order->usuario_id !== $request->auth_user->id) {
            return response()->json(['error' => 'Acesso não autorizado'], 403);
        }

        $order->delete();

        return response()->json(['message' => 'Pedido excluído com sucesso']);
    }
}