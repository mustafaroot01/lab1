<?php

namespace App\Console\Commands;

use App\Models\Chat\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateChatAttachmentsToPrivate extends Command
{
    protected $signature = 'chat:migrate-attachments
                            {--from=public : القرص المصدر للمرفقات القديمة}
                            {--to=local : القرص الخاص الوجهة}
                            {--dry-run : عرض ما سيتم نقله دون تنفيذ فعلي}';

    protected $description = 'نقل مرفقات الدردشة القديمة من القرص العام إلى القرص الخاص وتحديث سجلاتها';

    public function handle(): int
    {
        $from   = (string) $this->option('from');
        $to     = (string) $this->option('to');
        $dryRun = (bool) $this->option('dry-run');

        if ($from === $to) {
            $this->error("القرص المصدر والوجهة متطابقان ({$from}). لا شيء لنقله.");

            return self::FAILURE;
        }

        $messages = Message::whereNotNull('attachment_path')
            ->where('attachment_disk', $from)
            ->get();

        if ($messages->isEmpty()) {
            $this->info("لا توجد مرفقات على القرص '{$from}' بحاجة للنقل.");

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[معاينة] ' : '') . "سيتم نقل {$messages->count()} مرفق من '{$from}' إلى '{$to}'.");

        $moved   = 0;
        $skipped = 0;
        $failed  = 0;

        foreach ($messages as $message) {
            $path = $message->attachment_path;

            if (!Storage::disk($from)->exists($path)) {
                $this->warn("• رسالة #{$message->id}: الملف غير موجود على المصدر ({$path}) — تخطّي.");
                $skipped++;
                continue;
            }

            // إذا كان موجوداً على الوجهة مسبقاً نكتفي بتحديث السجل
            $existsOnTarget = Storage::disk($to)->exists($path);

            if ($dryRun) {
                $this->line("• رسالة #{$message->id}: {$path}" . ($existsOnTarget ? ' (موجود على الوجهة)' : ''));
                $moved++;
                continue;
            }

            try {
                if (!$existsOnTarget) {
                    $stream = Storage::disk($from)->readStream($path);
                    Storage::disk($to)->writeStream($path, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }

                $message->update(['attachment_disk' => $to]);
                Storage::disk($from)->delete($path);

                $moved++;
            } catch (\Throwable $e) {
                $this->error("• رسالة #{$message->id}: فشل النقل — {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[معاينة] ' : '') . "اكتمل: نُقل {$moved} — تخطّي {$skipped} — فشل {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
