<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * 返回前台需要的站点配置（运费规则等）。
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'free_shipping_threshold' => (float) Setting::getValue('shipping.free_threshold', 69),
                'shipping_fee'            => (float) Setting::getValue('shipping.fee', 8.99),
            ],
        ]);
    }
}
