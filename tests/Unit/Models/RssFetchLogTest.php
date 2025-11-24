<?php

namespace Tests\Unit\Models;

use App\Models\RssFetchLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RssFetchLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログを作成できることをテスト
     */
    public function test_can_create_log(): void
    {
        $log = RssFetchLog::create([
            'fetched_at' => '2025-11-24 10:00:00',
            'status' => RssFetchLog::STATUS_SUCCESS,
            'articles_count' => 10,
        ]);

        $this->assertDatabaseHas('rss_fetch_logs', [
            'status' => 'success',
            'articles_count' => 10,
        ]);
    }

    /**
     * 最新のログを取得できることをテスト
     */
    public function test_can_get_latest_log(): void
    {
        // 複数のログを作成
        RssFetchLog::create([
            'fetched_at' => '2025-11-24 10:00:00',
            'status' => RssFetchLog::STATUS_SUCCESS,
            'articles_count' => 10,
        ]);

        RssFetchLog::create([
            'fetched_at' => '2025-11-24 11:00:00',
            'status' => RssFetchLog::STATUS_SUCCESS,
            'articles_count' => 15,
        ]);

        $latest = RssFetchLog::latest();

        $this->assertNotNull($latest);
        $this->assertEquals(15, $latest->articles_count);
    }

    /**
     * 最新の成功ログを取得できることをテスト
     */
    public function test_can_get_latest_success_log(): void
    {
        // 成功ログと失敗ログを作成
        RssFetchLog::create([
            'fetched_at' => '2025-11-24 10:00:00',
            'status' => RssFetchLog::STATUS_SUCCESS,
            'articles_count' => 10,
        ]);

        RssFetchLog::create([
            'fetched_at' => '2025-11-24 11:00:00',
            'status' => RssFetchLog::STATUS_FAILURE,
            'articles_count' => null,
            'error_message' => 'エラーが発生しました',
        ]);

        $latestSuccess = RssFetchLog::latestSuccess();

        $this->assertNotNull($latestSuccess);
        $this->assertEquals(10, $latestSuccess->articles_count);
        $this->assertTrue($latestSuccess->isSuccess());
    }

    /**
     * 成功判定メソッドのテスト
     */
    public function test_is_success_method(): void
    {
        $successLog = RssFetchLog::create([
            'fetched_at' => '2025-11-24 10:00:00',
            'status' => RssFetchLog::STATUS_SUCCESS,
            'articles_count' => 10,
        ]);

        $this->assertTrue($successLog->isSuccess());
        $this->assertFalse($successLog->isFailure());
    }

    /**
     * 失敗判定メソッドのテスト
     */
    public function test_is_failure_method(): void
    {
        $failureLog = RssFetchLog::create([
            'fetched_at' => '2025-11-24 10:00:00',
            'status' => RssFetchLog::STATUS_FAILURE,
            'error_message' => 'エラーが発生しました',
        ]);

        $this->assertTrue($failureLog->isFailure());
        $this->assertFalse($failureLog->isSuccess());
    }

    /**
     * 成功ログのみに絞り込むScopeメソッドをテスト
     */
    public function test_scope_success(): void
    {
        // 成功ログと失敗ログを作成
        RssFetchLog::create([
            'fetched_at' => '2025-11-24 10:00:00',
            'status' => RssFetchLog::STATUS_SUCCESS,
            'articles_count' => 10,
        ]);

        RssFetchLog::create([
            'fetched_at' => '2025-11-24 11:00:00',
            'status' => RssFetchLog::STATUS_FAILURE,
            'error_message' => 'エラー',
        ]);

        RssFetchLog::create([
            'fetched_at' => '2025-11-24 12:00:00',
            'status' => RssFetchLog::STATUS_SUCCESS,
            'articles_count' => 15,
        ]);

        $successLogs = RssFetchLog::success()->get();

        $this->assertCount(2, $successLogs);
    }

    /**
     * 失敗ログのみに絞り込むScopeメソッドをテスト
     */
    public function test_scope_failure(): void
    {
        // 成功ログと失敗ログを作成
        RssFetchLog::create([
            'fetched_at' => '2025-11-24 10:00:00',
            'status' => RssFetchLog::STATUS_SUCCESS,
            'articles_count' => 10,
        ]);

        RssFetchLog::create([
            'fetched_at' => '2025-11-24 11:00:00',
            'status' => RssFetchLog::STATUS_FAILURE,
            'error_message' => 'エラー1',
        ]);

        RssFetchLog::create([
            'fetched_at' => '2025-11-24 12:00:00',
            'status' => RssFetchLog::STATUS_FAILURE,
            'error_message' => 'エラー2',
        ]);

        $failureLogs = RssFetchLog::failure()->get();

        $this->assertCount(2, $failureLogs);
    }
}
