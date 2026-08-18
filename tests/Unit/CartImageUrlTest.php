<?php

namespace Tests\Unit;

use App\Models\ProductImage;
use App\Models\ProductSkc;
use App\Services\CartService;
use Tests\TestCase;

class CartImageUrlTest extends TestCase
{
    public function test_购物车图片URL_经过CDN基址拼装(): void
    {
        config()->set('app.image_base_url', 'https://cdn.arelonne.com');

        $skc = new ProductSkc(['color' => 'Black']);
        $skc->setRelation('images', collect([
            new ProductImage(['url' => '/images/products/x/pid/skc/img-01.jpg']),
        ]));

        $service = app(CartService::class);

        $this->assertSame(
            'https://cdn.arelonne.com/images/products/x/pid/skc/img-01.jpg',
            $service->resolveImageUrl($skc)
        );
    }

    public function test_无图SKC返回null(): void
    {
        $skc = new ProductSkc(['color' => 'Black']);
        $skc->setRelation('images', collect([]));

        $this->assertNull(app(CartService::class)->resolveImageUrl($skc));
        $this->assertNull(app(CartService::class)->resolveImageUrl(null));
    }
}
