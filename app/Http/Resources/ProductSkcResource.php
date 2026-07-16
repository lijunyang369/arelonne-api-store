<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSkcResource extends JsonResource
{
    /**
     * SKC（Stock Keeping Color）JSON 转换，含颜色图片。
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'color'     => $this->color,
            'color_hex' => $this->color_hex,
            'slug'      => $this->slug,
            'sort'      => $this->sort,
            'images'    => ProductImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
