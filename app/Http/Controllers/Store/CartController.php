<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * 计算购物车总价（含运费、税费）。
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function calculate(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Not implemented'], 200);
    }
}
