<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * 创建订单（Guest Checkout）。
     *
     * 写入 🇺🇸 本地 DB 后，推送到 🇨🇳 Admin 供运营处理。
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_email'  => 'required|email',
            'customer_name'   => 'required|string|max:255',
            'customer_phone'  => 'nullable|string',
            'shipping_address'=> 'required|array',
            'items'           => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $order = DB::transaction(function () use ($data) {
            // 计算金额
            $subtotal = 0;
            $items = [];

            foreach ($data['items'] as $input) {
                $variant = ProductVariant::find($input['variant_id']);
                $price = (float) ($variant->price ?? $variant->product->base_price);
                $qty = $input['quantity'];

                $items[] = [
                    'product_id'         => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name'       => $variant->product->name,
                    'sku'                => $variant->sku,
                    'color'              => $variant->color,
                    'size'               => $variant->size,
                    'price'              => $price,
                    'quantity'           => $qty,
                    'subtotal'           => $price * $qty,
                ];
                $subtotal += $price * $qty;
            }

            // 运费规则来自后台设置（缺失时回退默认值）
            $threshold  = (float) Setting::getValue('shipping.free_threshold', 50);
            $fee        = (float) Setting::getValue('shipping.fee', 5.99);
            $shippingFee = $subtotal >= $threshold ? 0 : $fee;

            // 生成订单号
            $orderNo = 'HLP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $order = Order::create([
                'order_no'         => $orderNo,
                'customer_email'   => $data['customer_email'],
                'customer_name'    => $data['customer_name'],
                'customer_phone'   => $data['customer_phone'] ?? null,
                'subtotal'         => $subtotal,
                'shipping_fee'     => $shippingFee,
                'tax'              => 0,
                'discount'         => 0,
                'total'            => $subtotal + $shippingFee,
                'currency'         => 'USD',
                'shipping_address' => $data['shipping_address'],
                'status'           => 'pending',
                'payment_status'   => 'unpaid',
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            return $order;
        });

        // 推送到 🇨🇳 Admin
        SyncService::pushAsync('/orders', $order->load('items')->toArray(), 'POST', 'admin');

        return response()->json([
            'data' => [
                'id'       => $order->id,
                'order_no' => $order->order_no,
                'total'    => $order->total,
                'status'   => $order->status,
            ],
        ], 201);
    }

    /**
     * 通过订单号查询订单状态。
     */
    public function show(string $orderNo): JsonResponse
    {
        $order = Order::where('order_no', $orderNo)
            ->with('items')
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json([
            'data' => [
                'order_no'   => $order->order_no,
                'status'     => $order->status,
                'total'      => $order->total,
                'created_at' => $order->created_at?->toIso8601String(),
                'items'      => $order->items->map(fn ($i) => [
                    'product_name' => $i->product_name,
                    'color'        => $i->color,
                    'size'         => $i->size,
                    'price'        => $i->price,
                    'quantity'     => $i->quantity,
                ]),
            ],
        ]);
    }
}
