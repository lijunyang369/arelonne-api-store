<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\ContactMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * 提交联系留言（限流在路由层：10 次/分钟/IP）。
     *
     * 先用原始值判断 Honeypot 蜜罐，未命中才校验业务字段；
     * 落库与通知编排下沉到 ContactMessageService。
     */
    public function store(Request $request): JsonResponse
    {
        // 蜜罐字段：真人不可见，机器人会填写。在 validate 之前读取原始值，
        // 避免超长/数组形态的蜜罐在到达此处前就被挡成 422 而暴露路径。
        $website = $request->input('website');

        if ($website !== null && $website !== '') {
            ContactMessageService::notifySuspicious($request->all());

            return $this->respondAccepted();
        }

        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|max:200',
            'phone'        => 'nullable|string|max:30',
            'order_number' => 'nullable|string|max:50',
            'reason'       => 'required|string|in:order_status,cancel_change,damaged_missing,returns_replacements,size_fit,other',
            'message'      => 'required|string|min:10|max:5000',
        ]);

        ContactMessageService::submit($data);

        return $this->respondAccepted();
    }

    /**
     * 成功响应（真实提交与蜜罐命中共用，对外不可区分）。
     */
    private function respondAccepted(): JsonResponse
    {
        return response()->json([
            'data' => [
                'message' => 'Message received.',
            ],
        ], 201);
    }
}
