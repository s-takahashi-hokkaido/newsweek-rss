<?php

namespace App\Console\Commands;

use App\Models\RssFetchLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupOldLogsCommand extends Command
{
    /**
     * コマンド名
     *
     * @var string
     */
    protected $signature = 'logs:cleanup
                          {--dry-run : 削除対象を表示するのみ（実際には削除しない）}';

    /**
     * コマンド説明
     *
     * @var string
     */
    protected $description = '保存期間を過ぎた古いRSS取得ログを削除';

    /**
     * コマンド実行
     *
     * @return int
     */
    public function handle(): int
    {
        $retentionDays = config('data_lifecycle.logs.retention_days');
        $chunkSize = config('data_lifecycle.logs.chunk_size');
        $cutoffDate = now()->subDays($retentionDays);

        $this->info("削除対象: {$cutoffDate->format('Y-m-d H:i:s')} より古いログ");

        // 削除対象件数を取得
        $targetCount = RssFetchLog::where('created_at', '<', $cutoffDate)->count();

        if ($targetCount === 0) {
            $this->info('削除対象のログはありません');
            Log::info('ログ削除: 対象なし');
            return self::SUCCESS;
        }

        $this->info("削除対象: {$targetCount}件");

        // Dry-runモード
        if ($this->option('dry-run')) {
            $this->warn('[Dry-run] 実際には削除しません');
            return self::SUCCESS;
        }

        // チャンク削除実行
        $deletedCount = 0;

        RssFetchLog::where('created_at', '<', $cutoffDate)
            ->chunkById($chunkSize, function ($logs) use (&$deletedCount) {
                $ids = $logs->pluck('id')->toArray();
                $deleted = RssFetchLog::whereIn('id', $ids)->delete();
                $deletedCount += $deleted;

                $this->info("削除中... ({$deletedCount}件)");
            });

        $this->info("削除完了: {$deletedCount}件");
        Log::info('ログ削除完了', [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate->toDateTimeString(),
        ]);

        return self::SUCCESS;
    }
}

