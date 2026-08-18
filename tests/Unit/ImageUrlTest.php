<?php

namespace Tests\Unit;

use App\Http\Resources\ProductImageResource;
use App\Models\ProductImage;
use App\Support\ImageUrl;
use Tests\TestCase;

class ImageUrlTest extends TestCase
{
    public function test_基址为空时_输出相对路径(): void
    {
        config()->set('app.image_base_url', '');

        $this->assertSame('/images/products/a.jpg', ImageUrl::absolute('/images/products/a.jpg'));
    }

    public function test_基址配置时_拼接完整URL与缩略图(): void
    {
        config()->set('app.image_base_url', 'https://cdn.arelonne.com');

        $this->assertSame('https://cdn.arelonne.com/images/products/a.jpg', ImageUrl::absolute('/images/products/a.jpg'));
        $this->assertSame('https://cdn.arelonne.com/images/products/a_480.webp', ImageUrl::thumb('/images/products/a.jpg'));
    }

    public function test_绝对URL原样返回(): void
    {
        config()->set('app.image_base_url', 'https://cdn.arelonne.com');

        $this->assertSame('https://cdn.shopify.com/x.jpg', ImageUrl::absolute('https://cdn.shopify.com/x.jpg'));
        $this->assertSame('https://cdn.shopify.com/x.jpg', ImageUrl::thumb('https://cdn.shopify.com/x.jpg'));
    }

    public function test_图片资源输出包含thumb_url(): void
    {
        config()->set('app.image_base_url', 'https://cdn.arelonne.com');

        $img = new ProductImage([
            'url'        => '/images/products/a.jpg',
            'alt'        => 'x',
            'sort'       => 0,
            'is_primary' => true,
        ]);

        $out = (new ProductImageResource($img))->resolve();

        $this->assertSame('https://cdn.arelonne.com/images/products/a_480.webp', $out['thumb_url']);
    }
}
