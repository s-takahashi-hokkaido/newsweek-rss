<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupOldArticlesCommand extends Command
{
    /**
     * コマンド名
     *
     * @var string
     */
    protected $signature = 'articles:cleanup
                          {--dry-run : 削除対象を表示するのみ（実際には削除しない）}';

    /**
     * コマンド説明
     *
     * @var string
     */
    protected $description = '保存期間を過ぎた古い記事を削除';

    /**
     * コマンド実行
     *
     * @return int
     */
    public function handle(): int
    {
        $retentionDays = config('data_lifecycle.articles.retention_days');
        $chunkSize = config('data_lifecycle.articles.chunk_size');
        $cutoffDate = now()->subDays($retentionDays);

        $this->info("削除対象: {$cutoffDate->format('Y-m-d H:i:s')} より古い記事");

        // 削除対象件数を取得
        $targetCount = Article::where('created_at', '<', $cutoffDate)->count();

        if ($targetCount === 0) {
            $this->info('削除対象の記事はありません');
            Log::info('記事削除: 対象なし');
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

        Article::where('created_at', '<', $cutoffDate)
            ->chunkById($chunkSize, function ($articles) use (&$deletedCount) {
                $ids = $articles->pluck('id')->toArray();
                $deleted = Article::whereIn('id', $ids)->delete();
                $deletedCount += $deleted;

                $this->info("削除中... ({$deletedCount}件)");
            });

        $this->info("削除完了: {$deletedCount}件");
        Log::info('記事削除完了', [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate->toDateTimeString(),
        ]);

        return self::SUCCESS;
    }
}

