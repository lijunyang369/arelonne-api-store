<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingSyncController extends Controller
{
    /**
     * 接收来自 Admin 的站点设置（upsert）。
     *
     * 仅内部同步使用，由 VerifySyncKey 中间件保护。
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'settings'           => 'required|array',
            'settings.*.key'     => 'required|string|max:255',
            'settings.*.value'   => 'required',
            // type 必须有规则才会出现在 validated() 结果中
            'settings.*.type'    => 'nullable|string',
        ]);

        // 值仅支持标量（字符串/数字/布尔），数组/对象直接拒绝，避免 (string) 强转产生脏数据
        foreach ($data['settings'] as $item) {
            $key   = $item['key'];
            $value = $item['value'];

            if (! is_scalar($value)) {
                return response()->json([
                    'message' => 'Setting values must be scalar.',
                    'errors'  => [$key => ['Setting values must be scalar.']],
                ], 422);
            }
        }

        // 运费设置必须为非负数（key 前缀 shipping. 的数值配置）
        foreach ($data['settings'] as $item) {
            if (str_starts_with($item['key'], 'shipping.') &&
                (! is_numeric($item['value']) || ! is_finite((float) $item['value']) || (float) $item['value'] < 0 || (float) $item['value'] > 100000)) {
                return response()->json([
                    'message' => 'Shipping settings must be non-negative numbers.',
                    'errors'  => [$item['key'] => ['Shipping settings must be non-negative numbers.']],
                ], 422);
            }
        }

        foreach ($data['settings'] as $item) {
            $key   = $item['key'];
            $value = $item['value'];
            // 信任 Admin 发送的 type（保留布尔语义），缺失时本地推导
            $sentType = $item['type'] ?? null;
            // group 取 key 前缀（如 "shipping.fee" → "shipping"）
            $group = str_contains($key, '.') ? explode('.', $key)[0] : 'general';
            $type  = $sentType ?: (is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'string'));

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value, 'type' => $type, 'group' => $group]
            );
        }

        return response()->json(['data' => ['synced' => count($data['settings'])]]);
    }
}
