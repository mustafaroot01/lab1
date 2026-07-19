<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat\Message;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatAttachmentController extends Controller
{
    /**
     * تقديم مرفق الدردشة من القرص الخاص عبر رابط موقّت موقّع.
     * الرابط نفسه (temporarySignedRoute) هو التصريح — يُتحقق منه بـ middleware 'signed'
     * وينتهي خلال 30 دقيقة، فلا تُكشف المرفقات الطبية للعامة بشكل دائم.
     */
    public function show(Message $message): StreamedResponse
    {
        abort_if(!$message->attachment_path, 404, 'لا يوجد مرفق لهذه الرسالة');

        $disk = $message->attachment_disk ?: 'local';

        abort_if(!Storage::disk($disk)->exists($message->attachment_path), 404, 'الملف غير موجود');

        return Storage::disk($disk)->download(
            $message->attachment_path,
            $message->attachment_name ?: basename($message->attachment_path)
        );
    }
}
