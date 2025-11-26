<?php

namespace Tests\Feature\Console\Commands;

use App\Models\RssFetchLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanupOldLogsCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_deletes_logs_older_than_90_days()
    {
        // 91日前のログ（削除される）
        RssFetchLog::factory()->create([
            'created_at' => now()->subDays(91),
        ]);

        // 90日前のログ（削除される）
        RssFetchLog::factory()->create([
            'created_at' => now()->subDays(90)->subSecond(),
        ]);

        // 89日前のログ（残る）
        RssFetchLog::factory()->create([
            'created_at' => now()->subDays(89),
        ]);

        $this->artisan('logs:cleanup')
            ->expectsOutput('削除対象: 2件')
            ->expectsOutput('削除完了: 2件')
            ->assertExitCode(0);

        $this->assertDatabaseCount('rss_fetch_logs', 1);
    }

    /**
     * @test
     */
    public function test_shows_message_when_no_target_to_delete()
    {
        RssFetchLog::factory()->create([
            'created_at' => now()->subDays(30),
        ]);

        $this->artisan('logs:cleanup')
            ->expectsOutput('削除対象のログはありません')
            ->assertExitCode(0);

        $this->assertDatabaseCount('rss_fetch_logs', 1);
    }

    /**
     * @test
     */
    public function test_dry_run_does_not_delete_actually()
    {
        RssFetchLog::factory()->create([
            'created_at' => now()->subDays(91),
        ]);

        $this->artisan('logs:cleanup --dry-run')
            ->expectsOutput('削除対象: 1件')
            ->expectsOutput('[Dry-run] 実際には削除しません')
            ->assertExitCode(0);

        $this->assertDatabaseCount('rss_fetch_logs', 1);
    }

    /**
     * @test
     */
    public function test_chunk_deletion_works_correctly()
    {
        // 50件の古いログを作成
        RssFetchLog::factory()->count(50)->create([
            'created_at' => now()->subDays(91),
        ]);

        // 10件の新しいログを作成
        RssFetchLog::factory()->count(10)->create([
            'created_at' => now()->subDays(30),
        ]);

        $this->artisan('logs:cleanup')
            ->expectsOutput('削除対象: 50件')
            ->expectsOutput('削除完了: 50件')
            ->assertExitCode(0);

        $this->assertDatabaseCount('rss_fetch_logs', 10);
    }

    /**
     * @test
     */
    public function test_boundary_value_keeps_exactly_90_days_old_logs()
    {
        $cutoffDate = now()->subDays(90);

        // 90日前ちょうど（残る）
        $log90 = RssFetchLog::factory()->create([
            'created_at' => $cutoffDate,
        ]);

        // 90日前より1秒古い（削除される）
        RssFetchLog::factory()->create([
            'created_at' => $cutoffDate->copy()->subSecond(),
        ]);

        $this->artisan('logs:cleanup')
            ->expectsOutput('削除対象: 1件')
            ->expectsOutput('削除完了: 1件')
            ->assertExitCode(0);

        $this->assertDatabaseCount('rss_fetch_logs', 1);

        // 残っているログが90日前ちょうどのものであることを確認
        $remaining = RssFetchLog::first();
        $this->assertEquals($log90->id, $remaining->id);
    }
}

