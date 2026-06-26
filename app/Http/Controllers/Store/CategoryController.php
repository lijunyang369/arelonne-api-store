<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * 获取所有激活的分类（树形结构）。
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = Category::where('status', 'active')
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }
}
