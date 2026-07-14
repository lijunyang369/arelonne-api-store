<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * 尺码指南控制器 — 返回品牌通用身体测量数据。
 *
 * 数据来源于 config/sizeguide.php，只读。
 */
class SizeGuideController extends Controller
{
    /**
     * 获取品牌通用尺码指南。
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => [
                'body_measurements' => config('sizeguide.body_measurements'),
            ],
        ]);
    }
}
