<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    private $apiUrl = 'https://api.exchangerate.host';

    private $fallbackRates = [
        'BRL_USD' => 0.18,
        'USD_BRL' => 5.50,
    ];

    public function convert($amount, $from, $to)
    {
        $cacheKey = "currency_{$from}_{$to}";

        try {
            $rate = Cache::remember($cacheKey, 3600, function () use ($from, $to) {
                $url = "{$this->apiUrl}/convert?from={$from}&to={$to}";
                
                $response = Http::timeout(5)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['result']) && is_numeric($data['result'])) {
                        Log::info('Taxa de câmbio obtida da API', [
                            'from' => $from,
                            'to' => $to,
                            'rate' => $data['result']
                        ]);
                        return $data['result'];
                    }
                }
                
                $fallbackRate = $this->getFallbackRate($from, $to);
                Log::warning('Usando taxa de câmbio fallback', [
                    'from' => $from,
                    'to' => $to,
                    'rate' => $fallbackRate,
                    'status' => $response->status() ?? 'timeout'
                ]);
                
                return $fallbackRate;
            });

            return $rate ? $amount * $rate : $amount * $this->getFallbackRate($from, $to);

        } catch (\Exception $e) {
            Log::error('Erro na conversão de moeda, usando fallback: ' . $e->getMessage());
            return $amount * $this->getFallbackRate($from, $to);
        }
    }

    public function getOrderWithConversions($order)
    {
        $originalValue = (float) $order->valor;
        $originalCurrency = $order->moeda;

        if ($originalCurrency === 'BRL') {
            $usdValue = $this->convert($originalValue, 'BRL', 'USD');
            $order->valor_usd = round($usdValue, 2);
            $order->valor_brl = $originalValue;
        } else {
            $brlValue = $this->convert($originalValue, 'USD', 'BRL');
            $order->valor_brl = round($brlValue, 2);
            $order->valor_usd = $originalValue;
        }

        return $order;
    }

    public function testConnection()
    {
        try {
            $response = Http::timeout(3)->get("{$this->apiUrl}/latest?base=USD");
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('API de conversão de moeda indisponível: ' . $e->getMessage());
            return false;
        }
    }

    private function getFallbackRate($from, $to)
    {
        $key = "{$from}_{$to}";
        return $this->fallbackRates[$key] ?? 1; // Retorna 1 se não encontrar fallback
    }
}