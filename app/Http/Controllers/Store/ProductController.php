<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * 商品列表（支持按分类、价格区间筛选，分页返回）。
     *
     * @param  Request  $request  可选筛选：category, search, price_min, price_max, sort, per_page
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('status', 'active')
            ->with(['category', 'images']);

        // 按分类筛选
        if ($category = $request->get('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        // 搜索
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // 价格区间
        if ($min = $request->get('price_min')) {
            $query->where('base_price', '>=', $min);
        }
        if ($max = $request->get('price_max')) {
            $query->where('base_price', '<=', $max);
        }

        // 排序
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $allowedSorts = ['created_at', 'base_price', 'name', 'sort'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
        }

        $products = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => ProductListResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
                'last_page'    => $products->lastPage(),
            ],
        ]);
    }

    /**
     * 商品详情（通过 slug 查找，含变体和图片）。
     *
     * @param  string  $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with(['category', 'variants', 'skcs.images'])
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }
}
