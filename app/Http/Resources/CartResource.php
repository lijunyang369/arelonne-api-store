<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * 转换购物车为 API 响应。
     */
    public function toArray(Request $request): array
    {
        return [
            'guest_id' => $this->resource['guest_id'],
            'items'    => $this->resource['items'],
        ];
    }
}
