<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * 创建测试商品数据：2 个分类 + 5 个商品 + 变体。
     * 用于验证前端商品列表/详情 API。
     */
    public function run(): void
    {
        // ---- 分类 ----
        $bras = Category::updateOrCreate(
            ['slug' => 'bras'],
            ['name' => 'Bras & Innerwear', 'sort' => 1, 'status' => 'active'],
        );
        $linen = Category::updateOrCreate(
            ['slug' => 'linen'],
            ['name' => 'Cotton & Linen', 'sort' => 2, 'status' => 'active'],
        );

        // ---- 商品 0: Arelonne 生成链路样板 ----
        $p0 = Product::updateOrCreate(['slug' => 'halter-neck-banded-hem-bra-tank'], [
            'name' => 'Halter Neck Banded Hem Bra Tank',
            'description' => 'A soft halter-neck bra tank with a smooth built-in bra, clean neckline binding, and a relaxed banded hem for everyday ease.',
            'category_id' => $bras->id,
            'base_price' => 49.00,
            'cost_price' => 12.00,
            'status' => 'active',
            'sort' => 1,
            'meta' => [
                'fabric' => 'Soft stretch knit',
                'care' => 'Machine wash cold, lay flat to dry',
                'media_logic' => '真人主图确认后保留为主图；3D 图负责商品结构准确性；细节图覆盖领口走线和下摆捆条。',
            ],
        ]);
        $p0->images()->delete();
        if (! $p0->variants()->exists()) {
            $this->addVariants($p0, 'HBT', ['Olive'], ['S', 'M', 'L', 'XL']);
        }
        $this->addImage($p0, '/images/products/halter-neck-banded-hem-bra-tank/01-main-model.jpg', 'Halter Neck Banded Hem Bra Tank - model front', true);
        $this->addImage($p0, '/images/products/halter-neck-banded-hem-bra-tank/03-3d-front.jpg', 'Halter Neck Banded Hem Bra Tank - 3D front');
        $this->addImage($p0, '/images/products/halter-neck-banded-hem-bra-tank/04-3d-inside-bra.jpg', 'Halter Neck Banded Hem Bra Tank - built-in bra structure');
        $this->addImage($p0, '/images/products/halter-neck-banded-hem-bra-tank/05-detail-neckline-binding.jpg', 'Halter Neck Banded Hem Bra Tank - neckline binding detail');
        $this->addImage($p0, '/images/products/halter-neck-banded-hem-bra-tank/06-detail-banded-hem.jpg', 'Halter Neck Banded Hem Bra Tank - banded hem detail');
        $this->addImage($p0, '/images/products/halter-neck-banded-hem-bra-tank/07-color-olive.jpg', 'Halter Neck Banded Hem Bra Tank - olive color render');

        // ---- 商品 1: Bra ----
        $p1 = Product::updateOrCreate(['slug' => 'cloudsoft-wireless-bra'], [
            'name' => 'CloudSoft Wireless Bra',
            'description' => 'A wire-free bra that feels like you\'re wearing nothing. Breathable cotton-modal blend with gentle support for all-day comfort.',
            'category_id' => $bras->id,
            'base_price' => 49.00,
            'cost_price' => 12.00,
            'status' => 'active',
            'sort' => 2,
            'meta' => ['fabric' => 'Cotton-Modal Blend', 'care' => 'Machine wash cold, lay flat to dry'],
        ]);
        $p1->images()->delete();
        $this->addVariants($p1, 'BRA', ['Black', 'Nude', 'Dusty Rose'], ['S', 'M', 'L', 'XL']);
        $this->addImage($p1, 'https://picsum.photos/seed/bra1/600/800', 'CloudSoft Bra — Front', true);
        $this->addImage($p1, 'https://picsum.photos/seed/bra2/600/800', 'CloudSoft Bra — Side');

        // ---- 商品 2: Linen Shirt ----
        $p2 = Product::updateOrCreate(['slug' => 'relaxed-linen-shirt'], [
            'name' => 'Relaxed Linen Shirt',
            'description' => 'A lightweight, relaxed-fit linen shirt that breathes in warm weather. Perfect for layering or wearing on its own.',
            'category_id' => $linen->id,
            'base_price' => 68.00,
            'cost_price' => 18.00,
            'status' => 'active',
            'sort' => 3,
            'meta' => ['fabric' => '100% French Linen', 'care' => 'Machine wash gentle, tumble dry low'],
        ]);
        $p2->images()->delete();
        $this->addVariants($p2, 'LIN', ['White', 'Natural', 'Sage Green'], ['S', 'M', 'L']);
        $this->addImage($p2, 'https://picsum.photos/seed/linen1/600/800', 'Linen Shirt — Front', true);

        // ---- 商品 3: Cotton Tank ----
        $p3 = Product::updateOrCreate(['slug' => 'essential-cotton-tank'], [
            'name' => 'Essential Cotton Tank',
            'description' => 'The perfect layering piece. Soft, stretchy organic cotton with a flattering fit. Wear it under anything or on its own.',
            'category_id' => $bras->id,
            'base_price' => 35.00,
            'cost_price' => 8.00,
            'status' => 'active',
            'sort' => 4,
            'meta' => ['fabric' => 'Organic Cotton', 'care' => 'Machine wash cold'],
        ]);
        $p3->images()->delete();
        $this->addVariants($p3, 'TNK', ['White', 'Black', 'Grey'], ['XS', 'S', 'M', 'L']);
        $this->addImage($p3, 'https://picsum.photos/seed/tank1/600/800', 'Cotton Tank — Front', true);

        // ---- 商品 4: Linen Dress ----
        $p4 = Product::updateOrCreate(['slug' => 'breezy-linen-midi-dress'], [
            'name' => 'Breezy Linen Midi Dress',
            'description' => 'An effortless midi dress in pure linen. Side pockets, relaxed silhouette — made for sunny days and weekend getaways.',
            'category_id' => $linen->id,
            'base_price' => 89.00,
            'sale_price' => 79.00,
            'cost_price' => 22.00,
            'status' => 'active',
            'sort' => 5,
            'meta' => ['fabric' => '100% European Linen', 'care' => 'Hand wash or dry clean'],
        ]);
        $p4->images()->delete();
        $this->addVariants($p4, 'LDR', ['White', 'Navy', 'Terracotta'], ['S', 'M', 'L']);
        $this->addImage($p4, 'https://picsum.photos/seed/dress1/600/800', 'Linen Dress — Front', true);

        $this->command?->info('✅ 5 test products seeded with variants and images.');
    }

    /**
     * 为商品批量创建变体。
     */
    private function addVariants(Product $product, string $prefix, array $colors, array $sizes): void
    {
        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                ProductVariant::updateOrCreate(
                    ['sku' => "HLP-{$prefix}-{$color}-{$size}"],
                    [
                        'product_id' => $product->id,
                        'color'      => $color,
                        'size'       => $size,
                        'price'      => null,  // 使用 product base_price
                        'stock'      => rand(5, 50),
                    ],
                );
            }
        }
    }

    /**
     * 为商品添加一张图片。
     */
    private function addImage(Product $product, string $url, string $alt = '', bool $isPrimary = false): void
    {
        $maxSort = $product->images()->max('sort');
        $sort = $maxSort === null ? 0 : (int) $maxSort + 1;

        ProductImage::create([
            'product_id' => $product->id,
            'url'        => $url,
            'alt'        => $alt,
            'sort'       => $sort++,
            'is_primary' => $isPrimary,
        ]);
    }
}
