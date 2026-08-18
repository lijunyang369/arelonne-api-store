<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * 获取前台可见分类树:根与子都必须 active;
     * 无子分类的结构叶子(如 Skirts)保留;有子分类但 active 子全停用的根不返回(其下无可见商品,避免空集合页)。
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = Category::where('status', 'active')
            ->whereNull('parent_id')
            ->where(function ($query) {
                // 保留两类根:没有子分类的结构叶子;或至少有一个 active 子分类
                $query->doesntHave('children')
                    ->orWhereHas('children', fn ($q) => $q->where('status', 'active'));
            })
            ->with(['children' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }
}
