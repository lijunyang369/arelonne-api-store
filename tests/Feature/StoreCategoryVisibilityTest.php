<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCategoryVisibilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * api-store 不持有 migrations(schema 由 api-admin 唯一持有),
     * 测试迁移统一指向 api-admin 仓的 migrations 目录(相对本仓 ../..)。
     */
    protected function migrateFreshUsing(): array
    {
        return [
            '--drop-views' => false,
            '--drop-types' => false,
            '--path'       => dirname(__DIR__, 3).'/api-admin/database/migrations',
            '--realpath'   => true,
        ];
    }

    /** 停用子分类不出现在分类 API */
    public function test_inactive_child_is_hidden(): void
    {
        $root = Category::create(['name' => 'Tops', 'slug' => 'tops', 'sort' => 0, 'status' => 'active']);
        Category::create(['name' => 'Brami', 'slug' => 'brami-tops', 'parent_id' => $root->id, 'sort' => 0, 'status' => 'active']);
        Category::create(['name' => 'Hidden', 'slug' => 'hidden-tops', 'parent_id' => $root->id, 'sort' => 1, 'status' => 'inactive']);

        $res = $this->getJson('/api/store/categories');
        $res->assertOk();
        $children = $res->json('data.0.children');
        $this->assertCount(1, $children);
        $this->assertSame('brami-tops', $children[0]['slug']);
    }

    /** 所有子分类都停用的父分类不出现在分类 API */
    public function test_parent_with_all_inactive_children_is_hidden(): void
    {
        $root = Category::create(['name' => 'Dresses', 'slug' => 'dresses', 'sort' => 1, 'status' => 'active']);
        Category::create(['name' => 'Hidden', 'slug' => 'hidden-dresses', 'parent_id' => $root->id, 'sort' => 0, 'status' => 'inactive']);

        $res = $this->getJson('/api/store/categories');
        $this->assertNotContains('dresses', array_column($res->json('data'), 'slug'));
    }

    /** 无子分类的结构叶子(Skirts 模式)保留在分类 API */
    public function test_structural_leaf_without_children_is_kept(): void
    {
        Category::create(['name' => 'Skirts', 'slug' => 'skirts', 'sort' => 2, 'status' => 'active']);

        $res = $this->getJson('/api/store/categories');
        $this->assertContains('skirts', array_column($res->json('data'), 'slug'));
    }

    /** 商品 API 按停用分类 slug 筛选返回空 */
    public function test_product_filter_by_inactive_category_returns_empty(): void
    {
        $leaf = Category::create(['name' => 'Old', 'slug' => 'old', 'sort' => 0, 'status' => 'inactive']);
        Product::create(['name' => 'P', 'slug' => 'p', 'category_id' => $leaf->id,
            'base_price' => 10, 'status' => 'active', 'sort' => 0, 'meta' => []]);

        $res = $this->getJson('/api/store/products?category=old');
        $this->assertSame(0, $res->json('meta.total'));
    }
}
