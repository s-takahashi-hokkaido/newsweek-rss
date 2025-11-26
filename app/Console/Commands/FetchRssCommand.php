<?php

namespace App\Console\Commands;

use App\Models\RssFetchLog;
use App\Services\RssFeedService;
use Illuminate\Console\Command;

/**
 * RSSフィード取得コマンド
 * 
 * ニューズウィーク日本版のRSSフィードから記事を取得してデータベースに保存する
 */
class FetchRssCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rss:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ニューズウィーク日本版のRSSフィードから記事を取得';

    /**
     * RSSフィード取得サービス
     */
    private RssFeedService $rssFeedService;

    /**
     * コンストラクタ
     */
    public function __construct(RssFeedService $rssFeedService)
    {
        parent::__construct();
        $this->rssFeedService = $rssFeedService;
    }

    /**
     * Execute the console command.
     *
     * @return int Exit code (0: 成功, 1: 失敗)
     */
    public function handle(): int
    {
        $this->info('RSS取得を開始します...');
        
        $fetchedAt = now();
        
        // RSSフィード取得実行
        $result = $this->rssFeedService->fetch();
        
        // RssFetchLogに記録
        $this->recordLog($fetchedAt, $result);
        
        // 結果をコンソールに出力
        if ($result['success']) {
            $this->info("RSS取得完了: {$result['count']}件の新規記事を保存しました");
            return Command::SUCCESS;
        } else {
            $this->error("RSS取得失敗: {$result['error']}");
            return Command::FAILURE;
        }
    }

    /**
     * RSS取得結果をログテーブルに記録
     *
     * @param \Illuminate\Support\Carbon $fetchedAt 取得実行日時
     * @param array{success: bool, count: int, error: string|null} $result 取得結果
     * @return void
     */
    private function recordLog($fetchedAt, array $result): void
    {
        RssFetchLog::create([
            'fetched_at' => $fetchedAt,
            'status' => $result['success'] ? RssFetchLog::STATUS_SUCCESS : RssFetchLog::STATUS_FAILURE,
            'articles_count' => $result['count'],
            'error_message' => $result['error'],
        ]);
    }
}
