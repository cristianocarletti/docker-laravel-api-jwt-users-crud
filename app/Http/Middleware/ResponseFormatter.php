<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseFormatter
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Verifica se a resposta é um JSON
        if ($response instanceof Response && $response->headers->get('Content-Type') === 'application/json') {
            $content = json_decode($response->getContent(), true);

            // Define o status de sucesso ou erro
            if (isset($content['status']) && $content['status'] === 'success') {
                $response->setStatusCode($content['code'] ?? 200); // Código de sucesso padrão 200
            } elseif (isset($content['status']) && $content['status'] === 'error') {
                $response->setStatusCode($content['code'] ?? 400); // Código de erro padrão 400
            }
        }

        return $response;
    }
}
