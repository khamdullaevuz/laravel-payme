<?php

namespace Khamdullaevuz\Payme\Traits;

use Illuminate\Http\JsonResponse;

trait JsonRPC
{
    public function success($result): JsonResponse
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'result' => $result,
        ]);
    }

    public function error($error): JsonResponse
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'error' => $error,
        ]);
    }
}