<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * 同步购物车 — 客户端推送商品列表，服务端全量替换存储。
     */
    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'guest_id'        => 'required|string|max:64',
            'items'           => 'required|array',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $result = $this->cartService->sync($data['guest_id'], $data['items']);

        return response()->json([
            'data' => new CartResource($result),
        ]);
    }

    /**
     * 获取当前访客的购物车。
     */
    public function show(Request $request): JsonResponse
    {
        $guestId = $request->header('X-Guest-Id') ?: $request->query('guest_id');

        if (! $guestId) {
            return response()->json(['message' => 'Missing guest id.'], 400);
        }

        $result = $this->cartService->show($guestId);

        if ($result === null) {
            return response()->json([
                'data' => ['guest_id' => $guestId, 'items' => []],
            ]);
        }

        return response()->json([
            'data' => new CartResource($result),
        ]);
    }

    /**
     * 计算购物车总价（含运费、税费）。
     *
     * 服务端重新查价，不信任客户端传入的价格。
     */
    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items'           => 'required|array',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $result = $this->cartService->calculate($data['items']);

        return response()->json([
            'data' => $result,
        ]);
    }
}
