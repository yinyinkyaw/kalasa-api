<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function successResponse($message = '', $data = [], $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function nullResponse($message = '', $data = [], $code = 200)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => []
        ], $code);
    }

    protected function failResponse($message = '', $data = [], $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function validatorErrorResponse($message = '', $data = [], $code = 422)
    {
        return response()->json([
            'success'   => false,
            'message'   => $message,
            'data'      => $data
        ], $code);
    }
}
