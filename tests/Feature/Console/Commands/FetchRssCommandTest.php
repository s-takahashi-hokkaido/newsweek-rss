<?php

namespace Tests\Feature\Console\Commands;

use App\Models\RssFetchLog;
use App\Services\RssFeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FetchRssCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * コマンド実行成功時のテスト
     */
    public function test_command_executes_successfully_with_new_articles()
    {
        // RssFeedServiceをモック化
        $mockService = $this->createMock(RssFeedService::class);
        $mockService->method('fetch')->willReturn([
            'success' => true,
            'count' => 15,
            'error' => null,
        ]);

        $this->app->instance(RssFeedService::class, $mockService);

        // コマンド実行
        $this->artisan('rss:fetch')
            ->expectsOutput('RSS取得を開始します...')
            ->expectsOutput('RSS取得完了: 15件の新規記事を保存しました')
            ->assertExitCode(0);

        // RssFetchLogに記録されていることを確認
        $this->assertDatabaseCount('rss_fetch_logs', 1);
        
        $log = RssFetchLog::latest()->first();
        $this->assertEquals(RssFetchLog::STATUS_SUCCESS, $log->status);
        $this->assertEquals(15, $log->articles_count);
        $this->assertNull($log->error_message);
    }

    /**
     * コマンド実行成功（0件）
     */
    public function test_command_executes_successfully_with_zero_articles()
    {
        // RssFeedServiceをモック化（0件）
        $mockService = $this->createMock(RssFeedService::class);
        $mockService->method('fetch')->willReturn([
            'success' => true,
            'count' => 0,
            'error' => null,
        ]);

        $this->app->instance(RssFeedService::class, $mockService);

        // コマンド実行
        $this->artisan('rss:fetch')
            ->expectsOutput('RSS取得を開始します...')
            ->expectsOutput('RSS取得完了: 0件の新規記事を保存しました')
            ->assertExitCode(0);

        // RssFetchLogに成功として記録
        $log = RssFetchLog::latest()->first();
        $this->assertEquals(RssFetchLog::STATUS_SUCCESS, $log->status);
        $this->assertEquals(0, $log->articles_count);
    }

    /**
     * コマンド実行失敗時のテスト
     */
    public function test_command_fails_and_logs_error()
    {
        // RssFeedServiceをモック化（失敗）
        $mockService = $this->createMock(RssFeedService::class);
        $mockService->method('fetch')->willReturn([
            'success' => false,
            'count' => 0,
            'error' => 'HTTPリクエストタイムアウト',
        ]);

        $this->app->instance(RssFeedService::class, $mockService);

        // コマンド実行
        $this->artisan('rss:fetch')
            ->expectsOutput('RSS取得を開始します...')
            ->expectsOutput('RSS取得失敗: HTTPリクエストタイムアウト')
            ->assertExitCode(1);

        // RssFetchLogに失敗として記録
        $this->assertDatabaseCount('rss_fetch_logs', 1);
        
        $log = RssFetchLog::latest()->first();
        $this->assertEquals(RssFetchLog::STATUS_FAILURE, $log->status);
        $this->assertEquals(0, $log->articles_count);
        $this->assertEquals('HTTPリクエストタイムアウト', $log->error_message);
    }

    /**
     * 複数回実行時のログ記録テスト
     */
    public function test_multiple_executions_create_multiple_logs()
    {
        $mockService = $this->createMock(RssFeedService::class);
        $mockService->method('fetch')->willReturn([
            'success' => true,
            'count' => 5,
            'error' => null,
        ]);

        $this->app->instance(RssFeedService::class, $mockService);

        // 3回実行
        $this->artisan('rss:fetch')->assertExitCode(0);
        $this->artisan('rss:fetch')->assertExitCode(0);
        $this->artisan('rss:fetch')->assertExitCode(0);

        // 3件のログが記録されている
        $this->assertDatabaseCount('rss_fetch_logs', 3);
    }

    /**
     * fetched_atが正しく記録されているかテスト
     */
    public function test_fetched_at_is_recorded_correctly()
    {
        $mockService = $this->createMock(RssFeedService::class);
        $mockService->method('fetch')->willReturn([
            'success' => true,
            'count' => 10,
            'error' => null,
        ]);

        $this->app->instance(RssFeedService::class, $mockService);

        $beforeExecution = now()->subSecond(); // 1秒の余裕を持たせる
        $this->artisan('rss:fetch')->assertExitCode(0);
        $afterExecution = now()->addSecond();

        $log = RssFetchLog::latest()->first();
        
        // fetched_atが実行時刻の範囲内（Carbonインスタンスとして比較）
        $this->assertTrue($log->fetched_at->greaterThanOrEqualTo($beforeExecution));
        $this->assertTrue($log->fetched_at->lessThanOrEqualTo($afterExecution));
    }
}

