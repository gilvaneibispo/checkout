<?php

namespace App\Util;

class JsonView
{

    public static function show(array $info, $status = 200, $hasError = false, $errorMsg = null): array
    {
        $now = @(new \DateTime("now", new \DateTimeZone("America/Sao_Paulo")));

        return array(
            'status' => $status,
            'error' => array(
                'has_error' => $hasError,
                'message' => $errorMsg
            ),
            'time' => $now->format('Y-m-d H:i:s'),
            'data' => $info
        );
    }

    public static function error(\Exception $e): array
    {
        return array(
            "code" => $e->getCode(),
            "error" => $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine()
        );
    }

}