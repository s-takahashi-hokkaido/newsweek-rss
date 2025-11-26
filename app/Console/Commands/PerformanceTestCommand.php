<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PerformanceTestCommand extends Command
{
    protected $signature = 'test:performance';
    protected $description = '検索パフォーマンステストを実行（100万件データ対象）';

    public function handle(): int
    {
        $this->info('========================================');
        $this->info('パフォーマンステスト開始');
        $this->info('対象データ: ' . number_format(Article::count()) . '件');
        $this->info('========================================');
        $this->newLine();

        // テストパターン
        $tests = [
            '1. URL検索（完全一致）' => function () {
                return Article::byUrl('https://www.newsweekjapan.jp/stories/world/2024/article_500000.php')
                    ->newest()
                    ->paginate(20);
            },
            
            '2. タイトル検索（部分一致）' => function () {
                return Article::byTitle('米大統領')
                    ->newest()
                    ->paginate(20);
            },
            
            '3. 日付検索（From）' => function () {
                return Article::publishedFrom(now()->subDays(30))
                    ->newest()
                    ->paginate(20);
            },
            
            '4. 日付検索（To）' => function () {
                return Article::publishedTo(now()->subDays(30))
                    ->newest()
                    ->paginate(20);
            },
            
            '5. 日付範囲検索' => function () {
                return Article::publishedFrom(now()->subDays(60))
                    ->publishedTo(now()->subDays(30))
                    ->newest()
                    ->paginate(20);
            },
            
            '6. 複合検索（日付範囲 + タイトル）' => function () {
                return Article::publishedFrom(now()->subDays(60))
                    ->publishedTo(now()->subDays(30))
                    ->byTitle('経済')
                    ->newest()
                    ->paginate(20);
            },
            
            '7. 条件なし（全件取得）' => function () {
                return Article::newest()->paginate(20);
            },
        ];

        foreach ($tests as $name => $test) {
            $this->runTest($name, $test);
            $this->newLine();
        }

        $this->info('========================================');
        $this->info('パフォーマンステスト完了');
        $this->info('========================================');

        return self::SUCCESS;
    }

    private function runTest(string $name, callable $test): void
    {
        $this->info("【{$name}】");
        
        // クエリログをクリアして有効化
        DB::flushQueryLog();
        DB::enableQueryLog();
        
        // 実行時間を測定
        $start = microtime(true);
        $result = $test();
        $end = microtime(true);
        
        $executionTime = round(($end - $start) * 1000, 2); // ミリ秒
        
        // クエリログを取得
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        DB::flushQueryLog();
        
        // 結果表示
        $this->line("  実行時間: {$executionTime}ms");
        $this->line("  取得件数: {$result->total()}件");
        $this->line("  表示件数: {$result->count()}件（1ページ目）");
        $this->line("  総ページ数: {$result->lastPage()}ページ");
        
        // 主要なSQLクエリを表示
        if (count($queries) > 0) {
            $mainQuery = $queries[0]['query'];
            $this->line("  SQL: " . $this->shortenSql($mainQuery));
            $this->line("  Bindings: " . json_encode($queries[0]['bindings']));
        }
        
        // EXPLAIN実行
        if (count($queries) > 0) {
            $this->explain($queries[0]['query'], $queries[0]['bindings']);
        }
    }

    private function explain(string $sql, array $bindings): void
    {
        try {
            // SQLのバインディングを置換
            foreach ($bindings as $binding) {
                $value = is_string($binding) ? "'{$binding}'" : $binding;
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }
            
            $explain = DB::select("EXPLAIN {$sql}");
            
            if (count($explain) > 0) {
                $row = $explain[0];
                $this->line("  EXPLAIN:");
                $this->line("    type: {$row->type}");
                $this->line("    key: " . ($row->key ?? 'NULL'));
                $this->line("    rows: {$row->rows}");
                $this->line("    Extra: " . ($row->Extra ?? '-'));
            }
        } catch (\Exception $e) {
            $this->warn("  EXPLAIN実行エラー: " . $e->getMessage());
        }
    }

    private function shortenSql(string $sql): string
    {
        // SQLを短縮表示
        $sql = preg_replace('/\s+/', ' ', $sql);
        if (strlen($sql) > 100) {
            return substr($sql, 0, 100) . '...';
        }
        return $sql;
    }
}

