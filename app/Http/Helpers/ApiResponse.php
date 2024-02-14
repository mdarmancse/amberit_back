<?php
// ApiResponse.php

namespace App\Http\Helpers;

class ApiResponse
{
    public static function success($data = null,$totalCount=0, $message = 'Request was successful.')
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $totalCount,
            'message' => $message,
        ]);
    }

    public static function error($code, $message, $details = null)
    {
        return response()->json([
            'success' => false,
            'data' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $code);
    }

    public static function validationError($errors)
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
        ], 422);
    }
}
