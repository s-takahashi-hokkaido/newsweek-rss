<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * パフォーマンステスト用に大量のダミーデータを生成
     * 100万件のデータを約10分程度で生成
     */
    public function run(): void
    {
        $this->command->info('記事データの生成を開始します...');
        $this->command->info('目標: 1,000,000件');

        // 既存のデータ件数を確認
        $existingCount = Article::count();
        if ($existingCount > 0) {
            $this->command->warn("既に {$existingCount} 件のデータが存在します。");
            if (!$this->command->confirm('既存データを削除して再生成しますか？', false)) {
                $this->command->info('処理を中止しました。');
                return;
            }
            
            $this->command->info('既存データを削除中...');
            Article::truncate();
            $this->command->info('削除完了');
        }

        // バッチサイズ（一度にINSERTする件数）
        $batchSize = 1000;
        $totalRecords = 1000000;
        $batches = $totalRecords / $batchSize;

        $this->command->getOutput()->progressStart($batches);

        $startTime = microtime(true);

        for ($batch = 0; $batch < $batches; $batch++) {
            $records = [];
            
            for ($i = 0; $i < $batchSize; $i++) {
                $globalIndex = $batch * $batchSize + $i;
                
                // 日付の分散（過去1年間）
                $daysAgo = rand(0, 365);
                $timestamp = now()->subDays($daysAgo);
                
                $records[] = [
                    'url' => "https://www.newsweekjapan.jp/stories/world/2024/article_{$globalIndex}.php",
                    'title' => $this->generateTitle($globalIndex),
                    'content' => $this->generateContent($globalIndex),
                    'published_at' => $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            // 一括INSERT（高速化）
            DB::table('articles')->insert($records);
            
            $this->command->getOutput()->progressAdvance();

            // メモリ解放
            unset($records);
            
            // 10バッチごとにメモリ使用量を表示
            if (($batch + 1) % 10 === 0) {
                $memory = round(memory_get_usage(true) / 1024 / 1024, 2);
                $elapsed = round(microtime(true) - $startTime, 2);
                $recordsGenerated = ($batch + 1) * $batchSize;
                $recordsPerSecond = round($recordsGenerated / $elapsed);
                
                // プログレスバーを一時停止してメッセージ表示
                $this->command->getOutput()->progressFinish();
                $this->command->info(sprintf(
                    '  [%s件] メモリ: %sMB, 経過: %ss, 速度: %s件/秒',
                    number_format($recordsGenerated),
                    $memory,
                    $elapsed,
                    number_format($recordsPerSecond)
                ));
                $this->command->getOutput()->progressStart($batches);
                $this->command->getOutput()->progressAdvance($batch + 1);
            }
        }

        $this->command->getOutput()->progressFinish();

        $endTime = microtime(true);
        $totalTime = round($endTime - $startTime, 2);
        $avgPerSecond = round($totalRecords / $totalTime);

        $this->command->info('');
        $this->command->info('✓ データ生成完了！');
        $this->command->info("  総件数: " . number_format($totalRecords) . "件");
        $this->command->info("  所要時間: {$totalTime}秒");
        $this->command->info("  平均速度: " . number_format($avgPerSecond) . "件/秒");
        $this->command->info("  最終メモリ: " . round(memory_get_usage(true) / 1024 / 1024, 2) . "MB");
    }

    /**
     * タイトル生成
     */
    private function generateTitle(int $index): string
    {
        $templates = [
            '米大統領、新政策を発表 #{index}',
            '日本経済、好調を維持 - 専門家の見解 #{index}',
            'テクノロジー業界に変革の波 #{index}',
            '環境問題への取り組みが加速 #{index}',
            '教育改革の最新動向 #{index}',
            '医療技術の革新的な進歩 #{index}',
            '国際関係の新たな展開 #{index}',
            'エンターテインメント業界の最新ニュース #{index}',
            'スポーツ界を揺るがす大きな出来事 #{index}',
            '科学者たちが新発見を報告 #{index}',
        ];

        $template = $templates[$index % count($templates)];
        return str_replace('#{index}', $index, $template);
    }

    /**
     * コンテンツ生成
     */
    private function generateContent(int $index): string
    {
        $paragraphs = [
            '最新の調査によると、この分野における重要な進展が確認されました。専門家たちは、今後の展開に注目しています。',
            '関係者によれば、この動きは予想以上の影響をもたらす可能性があるとのことです。詳細な分析が進められています。',
            '業界のリーダーたちは、この状況に対して慎重な姿勢を示しています。今後の対応が注目されます。',
            '市場の反応は概ね好意的で、投資家たちは楽観的な見方を示しています。ただし、リスクも指摘されています。',
            '政府は新たな施策を検討しており、関連法案の提出も視野に入れています。各方面からの意見が集まっています。',
        ];

        $numParagraphs = ($index % 3) + 2; // 2-4段落
        $content = '';
        
        for ($i = 0; $i < $numParagraphs; $i++) {
            $content .= $paragraphs[($index + $i) % count($paragraphs)] . "\n\n";
        }

        return trim($content);
    }
}





