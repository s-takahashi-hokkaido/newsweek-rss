<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanupOldArticlesCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_deletes_articles_older_than_90_days()
    {
        // 91日前の記事（削除される）
        Article::factory()->create([
            'created_at' => now()->subDays(91),
            'published_at' => now()->subDays(91),
        ]);

        // 90日前の記事（削除される）
        Article::factory()->create([
            'created_at' => now()->subDays(90)->subSecond(),
            'published_at' => now()->subDays(90),
        ]);

        // 89日前の記事（残る）
        Article::factory()->create([
            'created_at' => now()->subDays(89),
            'published_at' => now()->subDays(89),
        ]);

        $this->artisan('articles:cleanup')
            ->expectsOutput('削除対象: 2件')
            ->expectsOutput('削除完了: 2件')
            ->assertExitCode(0);

        $this->assertDatabaseCount('articles', 1);
    }

    /**
     * @test
     */
    public function test_shows_message_when_no_target_to_delete()
    {
        Article::factory()->create([
            'created_at' => now()->subDays(30),
            'published_at' => now()->subDays(30),
        ]);

        $this->artisan('articles:cleanup')
            ->expectsOutput('削除対象の記事はありません')
            ->assertExitCode(0);

        $this->assertDatabaseCount('articles', 1);
    }

    /**
     * @test
     */
    public function test_dry_run_does_not_delete_actually()
    {
        Article::factory()->create([
            'created_at' => now()->subDays(91),
            'published_at' => now()->subDays(91),
        ]);

        $this->artisan('articles:cleanup --dry-run')
            ->expectsOutput('削除対象: 1件')
            ->expectsOutput('[Dry-run] 実際には削除しません')
            ->assertExitCode(0);

        $this->assertDatabaseCount('articles', 1);
    }

    /**
     * @test
     */
    public function test_chunk_deletion_works_correctly()
    {
        // 50件の古い記事を作成
        Article::factory()->count(50)->create([
            'created_at' => now()->subDays(91),
            'published_at' => now()->subDays(91),
        ]);

        // 10件の新しい記事を作成
        Article::factory()->count(10)->create([
            'created_at' => now()->subDays(30),
            'published_at' => now()->subDays(30),
        ]);

        $this->artisan('articles:cleanup')
            ->expectsOutput('削除対象: 50件')
            ->expectsOutput('削除完了: 50件')
            ->assertExitCode(0);

        $this->assertDatabaseCount('articles', 10);
    }

    /**
     * @test
     */
    public function test_boundary_value_keeps_exactly_90_days_old_articles()
    {
        $cutoffDate = now()->subDays(90);

        // 90日前ちょうど（残る）
        $article90 = Article::factory()->create([
            'created_at' => $cutoffDate,
            'published_at' => $cutoffDate,
        ]);

        // 90日前より1秒古い（削除される）
        Article::factory()->create([
            'created_at' => $cutoffDate->copy()->subSecond(),
            'published_at' => $cutoffDate->copy()->subSecond(),
        ]);

        $this->artisan('articles:cleanup')
            ->expectsOutput('削除対象: 1件')
            ->expectsOutput('削除完了: 1件')
            ->assertExitCode(0);

        $this->assertDatabaseCount('articles', 1);

        // 残っている記事が90日前ちょうどのものであることを確認
        $remaining = Article::first();
        $this->assertEquals($article90->id, $remaining->id);
    }

    /**
     * @test
     */
    public function test_deletes_based_on_created_at_not_published_at()
    {
        // created_atが91日前、published_atが30日前（削除される）
        Article::factory()->create([
            'created_at' => now()->subDays(91),
            'published_at' => now()->subDays(30),
        ]);

        // created_atが30日前、published_atが91日前（残る）
        Article::factory()->create([
            'created_at' => now()->subDays(30),
            'published_at' => now()->subDays(91),
        ]);

        $this->artisan('articles:cleanup')
            ->expectsOutput('削除対象: 1件')
            ->expectsOutput('削除完了: 1件')
            ->assertExitCode(0);

        $this->assertDatabaseCount('articles', 1);

        // 残っている記事が created_at 30日前のものであることを確認
        $remaining = Article::first();
        $this->assertEquals(now()->subDays(30)->format('Y-m-d'), $remaining->created_at->format('Y-m-d'));
    }
}

