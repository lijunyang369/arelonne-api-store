<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Setting;

class CartService
{
    /**
     * 同步客户端购物车数据到服务端。
     *
     * 全量替换模式：清空现有明细，按 payload 重新写入。
     * 同一 variant 去重（取最新）。
     *
     * @param  string  $guestId
     * @param  array   $items  [['variant_id' => int, 'quantity' => int], ...]
     * @return array   同步后的购物车完整数据
     */
    public function sync(string $guestId, array $items): array
    {
        $cart = Cart::firstOrCreate(['guest_id' => $guestId]);

        // 去重：同一 variant_id 只保留最后一次
        $deduped = [];
        foreach ($items as $item) {
            $deduped[$item['variant_id']] = $item['quantity'];
        }

        // 全量替换：删除旧明细，写入新明细
        $cart->items()->delete();

        foreach ($deduped as $variantId => $quantity) {
            $variant = ProductVariant::with('product')->find($variantId);
            if (! $variant || ! $variant->product) {
                continue;
            }

            $cart->items()->create([
                'product_id'         => $variant->product_id,
                'product_variant_id' => $variantId,
                // 与 calculate 保持一致：数量至少为 1
                'quantity'           => max(1, (int) $quantity),
            ]);
        }

        return $this->buildCartData($cart->fresh(['items.productVariant.product.category', 'items.productVariant.product.primarySkc.images']));
    }

    /**
     * 获取指定访客的购物车。
     *
     * @param  string  $guestId
     * @return array|null
     */
    public function show(string $guestId): ?array
    {
        $cart = Cart::where('guest_id', $guestId)->first();
        if (! $cart) {
            return null;
        }

        return $this->buildCartData($cart->load(['items.productVariant.product.category', 'items.productVariant.product.primarySkc.images']));
    }

    /**
     * 验价并计算购物车总价。
     *
     * 服务端重新查价，不信任客户端传入的价格。
     * 运费规则：满 $50 免运费，否则 $5.99。
     *
     * @param  array  $items  [['variant_id' => int, 'quantity' => int], ...]
     * @return array
     */
    public function calculate(array $items): array
    {
        $lineItems = [];
        $subtotal = 0;

        foreach ($items as $input) {
            $variant = ProductVariant::with('product')->find($input['variant_id']);
            $available = $variant !== null
                && $variant->product !== null
                && $variant->product->status === 'active'
                && $variant->stock > 0;

            $price = 0;
            if ($available) {
                $price = (float) ($variant->price ?? $variant->product->base_price);
            }

            $qty = max(1, (int) ($input['quantity'] ?? 1));
            $lineSubtotal = $price * $qty;
            $subtotal += $lineSubtotal;

            $lineItems[] = [
                'variant_id' => $input['variant_id'],
                'quantity'   => $qty,
                'price'      => number_format($price, 2, '.', ''),
                'subtotal'   => number_format($lineSubtotal, 2, '.', ''),
                'available'  => $available,
            ];
        }

        // 运费规则来自后台设置（缺失时回退默认值）
        $threshold = (float) Setting::getValue('shipping.free_threshold', 50);
        $fee       = (float) Setting::getValue('shipping.fee', 5.99);

        $shipping = $subtotal >= $threshold ? 0 : $fee;
        $total = $subtotal + $shipping;

        return [
            'items'                    => $lineItems,
            'subtotal'                 => number_format($subtotal, 2, '.', ''),
            'shipping'                 => number_format($shipping, 2, '.', ''),
            'total'                    => number_format($total, 2, '.', ''),
            'free_shipping_threshold'  => $threshold,
        ];
    }

    /**
     * 构建购物车 API 返回数据。
     */
    private function buildCartData(Cart $cart): array
    {
        $items = $cart->items->map(function (CartItem $item) {
            $variant = $item->productVariant;
            $product = $variant?->product;
            $price = (float) ($variant->price ?? $product?->base_price ?? 0);

            // 通过 product.primarySkc 获取首图（该颜色的第一张图）
            $imageUrl = null;
            $primarySkc = $product?->primarySkc;
            if ($primarySkc && $primarySkc->images->isNotEmpty()) {
                $imageUrl = $primarySkc->images->first()->url;
            }

            return [
                'id'            => $item->id,
                'variant_id'    => $item->product_variant_id,
                'product_id'    => $item->product_id,
                'product_name'  => $product?->name ?? '',
                'product_slug'  => $product?->slug ?? '',
                'category_slug' => $product?->category?->slug ?? '',
                'color'         => $variant?->color ?? '',
                'size'          => $variant?->size ?? '',
                'price'         => number_format($price, 2, '.', ''),
                'image_url'     => $imageUrl,
                'quantity'      => $item->quantity,
            ];
        });

        return [
            'guest_id' => $cart->guest_id,
            'items'    => $items->values()->toArray(),
        ];
    }
}
