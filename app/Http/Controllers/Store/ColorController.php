<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use Illuminate\Http\JsonResponse;

class ColorController extends Controller
{
    /**
     * 前端颜色列表 — 仅返回活跃颜色。
     * 用于构建商品色块选择器的 hex 映射表。
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => ColorResource::collection(
                Color::active()->orderBy('name')->get()
            ),
        ]);
    }
}
