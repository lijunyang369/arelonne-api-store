<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id', 'product_variant_id', 'url',
        'alt', 'sort', 'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort'       => 'integer',
        ];
    }

    /**
     * 所属商品。
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 所属变体（可为空，表示通用图片）。
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * 图片 URL accessor — 将旧平铺路径转为子目录路径。
     * 旧: /images/products/{base}-{NN}.{ext}
     * 新: /images/products/{base}/{base}-{NN}.{ext}
     * 外部 URL 或已是新目录结构的路径原样返回（幂等）。
     */
    public function getUrlAttribute(string $value): string
    {
        // 已是子目录结构，无需转换（幂等保护）
        if (preg_match('#^/images/products/[^/]+/#', $value)) {
            return $value;
        }

        // 旧平铺路径 → 子目录结构
        if (preg_match('#^/images/products/([^/]+)-(\d{2,})\.(jpg|jpeg|png|webp)$#i', $value, $m)) {
            $dir = $m[1]; // e.g., "plantive-bra-tank"
            return "/images/products/{$dir}/{$dir}-{$m[2]}.{$m[3]}";
        }

        return $value;
    }
}
