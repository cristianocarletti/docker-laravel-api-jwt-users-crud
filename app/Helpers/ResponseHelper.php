<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public static function success($data = [], $message = 'Solicitação bem-sucedida', $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function error($message = 'Falha na solicitação', $code = 400, $data = []): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function notFound($message = 'Recurso não encontrado', $data = []): JsonResponse
    {
        return self::error($message, 404, $data);
    }

    public static function forbidden($message = 'Proibido', $data = []): JsonResponse
    {
        return self::error($message, 403, $data);
    }
}
