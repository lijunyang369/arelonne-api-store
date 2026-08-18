<?php

namespace App\Services;

use App\Models\ContactMessage;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class ContactMessageService
{
    /** 客服收件邮箱 — 留言通知统一发到这里 */
    private const SUPPORT_EMAIL = 'support@arelonne.com';

    /**
     * 提交留言：落库并发通知邮件。
     *
     * 单库架构下 Admin 直接读共享库，邮件失败只记日志、不阻塞结果。
     */
    public static function submit(array $data): ContactMessage
    {
        $contact = ContactMessage::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'phone'        => $data['phone'] ?? null,
            'order_number' => $data['order_number'] ?? null,
            'reason'       => $data['reason'],
            'message'      => $data['message'],
            'status'       => 'new',
        ]);

        static::send(
            "New contact message — {$contact->name}",
            "New contact message from {$contact->name}\n\n"
            . "Name: {$contact->name}\n"
            . "Email: {$contact->email}\n"
            . 'Phone: ' . ($contact->phone ?? '—') . "\n"
            . 'Order number: ' . ($contact->order_number ?? '—') . "\n"
            . "Reason: {$contact->reason}\n\n"
            . "Message:\n{$contact->message}",
        );

        return $contact;
    }

    /**
     * 蜜罐命中的可疑提交：不落库，仅发送标注通知。
     *
     * 与正常提交同样走同步发信，保持两条路径响应时序一致，避免机器人反推蜜罐；
     * 同时客服能直接看到被拦截的垃圾请求。
     */
    public static function notifySuspicious(array $payload): void
    {
        static::send(
            'Suspicious contact submission blocked',
            "A bot-like submission was blocked by the honeypot.\n\n"
            . 'Website field: ' . static::scalarize($payload['website'] ?? null) . "\n"
            . 'Name: ' . static::scalarize($payload['name'] ?? null) . "\n"
            . 'Email: ' . static::scalarize($payload['email'] ?? null),
        );
    }

    /**
     * 把任意类型安全转为受限长度的字符串（数组等非标量转 JSON），
     * 防止未校验的蜜罐载荷在拼接时触发「Array to string conversion」异常。
     */
    private static function scalarize(mixed $value): string
    {
        if (! is_scalar($value)) {
            $value = json_encode($value) ?: '—';
        }

        return mb_substr((string) $value, 0, 300);
    }

    /**
     * 发送纯文本通知邮件。
     *
     * 用 Mail::raw 直出字符串，内容不经模板转义（避免撇号/& 被转成 HTML 实体）。
     * 同步发送：小流量下不依赖队列 worker；失败只记日志不向上抛出。
     */
    private static function send(string $subject, string $body): void
    {
        try {
            Mail::raw($body, fn (Message $message) => $message
                ->to(self::SUPPORT_EMAIL)
                ->subject($subject));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
