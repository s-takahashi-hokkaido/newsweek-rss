<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * テスト用に少量のダミーデータを生成
     */
    public function run(): void
    {
        $this->command->info('テストデータの生成を開始します...');
        
        // 生成件数（固定）
        $count = 10000;
        $this->command->info("目標: " . number_format($count) . "件");

        // 既存のデータ件数を確認
        $existingCount = Article::count();
        if ($existingCount > 0) {
            $this->command->warn("既に {$existingCount} 件のデータが存在します。");
        }

        $batchSize = 1000;
        $batches = ceil($count / $batchSize);

        $startTime = microtime(true);

        for ($batch = 0; $batch < $batches; $batch++) {
            $recordsInThisBatch = min($batchSize, $count - ($batch * $batchSize));
            $records = [];
            
            for ($i = 0; $i < $recordsInThisBatch; $i++) {
                $globalIndex = $batch * $batchSize + $i;
                
                // 日付の分散（過去1年間）
                $daysAgo = rand(0, 365);
                $timestamp = now()->subDays($daysAgo);
                
                $records[] = [
                    'url' => "https://www.newsweekjapan.jp/stories/test/article_{$globalIndex}.php",
                    'title' => $this->generateTitle($globalIndex),
                    'content' => $this->generateContent($globalIndex),
                    'published_at' => $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            DB::table('articles')->insert($records);
            
            $this->command->info("  [" . number_format(($batch + 1) * $batchSize) . "件] 生成中...");
        }

        $endTime = microtime(true);
        $totalTime = round($endTime - $startTime, 2);
        $avgPerSecond = round($count / $totalTime);

        $this->command->info('');
        $this->command->info('✓ データ生成完了！');
        $this->command->info("  総件数: " . number_format($count) . "件");
        $this->command->info("  所要時間: {$totalTime}秒");
        $this->command->info("  平均速度: " . number_format($avgPerSecond) . "件/秒");
    }

    private function generateTitle(int $index): string
    {
        $templates = [
            '米大統領、新政策を発表 #{index}',
            '日本経済、好調を維持 - 専門家の見解 #{index}',
            'テクノロジー業界に変革の波 #{index}',
            '環境問題への取り組みが加速 #{index}',
            '教育改革の最新動向 #{index}',
        ];

        $template = $templates[$index % count($templates)];
        return str_replace('#{index}', $index, $template);
    }

    private function generateContent(int $index): string
    {
        return '最新の調査によると、この分野における重要な進展が確認されました。専門家たちは、今後の展開に注目しています。';
    }
}

