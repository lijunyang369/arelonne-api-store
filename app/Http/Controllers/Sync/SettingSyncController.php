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
        ]);

        foreach ($data['settings'] as $item) {
            $key   = $item['key'];
            $value = $item['value'];
            // group 取 key 前缀（如 "shipping.fee" → "shipping"）
            $group = str_contains($key, '.') ? explode('.', $key)[0] : 'general';
            $type  = is_numeric($value) ? 'number' : 'string';

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value, 'type' => $type, 'group' => $group]
            );
        }

        return response()->json(['data' => ['synced' => count($data['settings'])]]);
    }
}
