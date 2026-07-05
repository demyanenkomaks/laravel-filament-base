<?php

declare(strict_types=1);

namespace Modules\ApiV1\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiV1Controller extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'API check works!',
            'version' => '1.0.0',
        ]);
    }
}
