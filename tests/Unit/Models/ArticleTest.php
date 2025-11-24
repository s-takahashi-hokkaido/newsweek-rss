<?php

namespace Tests\Unit\Models;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 記事を作成できることをテスト
     */
    public function test_can_create_article(): void
    {
        $article = Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'テスト記事',
            'content' => 'これはテスト記事の内容です。',
            'published_at' => '2025-11-24 10:00:00',
        ]);

        $this->assertDatabaseHas('articles', [
            'url' => 'https://example.com/article/1',
            'title' => 'テスト記事',
        ]);
    }

    /**
     * URL検索のScopeメソッドをテスト
     */
    public function test_scope_by_url(): void
    {
        // テストデータ作成
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => '記事1',
            'content' => '内容1',
            'published_at' => '2025-11-24 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => '記事2',
            'content' => '内容2',
            'published_at' => '2025-11-24 11:00:00',
        ]);

        // URL検索
        $result = Article::byUrl('https://example.com/article/1')->first();

        $this->assertNotNull($result);
        $this->assertEquals('記事1', $result->title);
    }

    /**
     * タイトル検索のScopeメソッドをテスト
     */
    public function test_scope_by_title(): void
    {
        // テストデータ作成
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'Laravel フレームワーク入門',
            'content' => '内容1',
            'published_at' => '2025-11-24 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => 'PHP プログラミング基礎',
            'content' => '内容2',
            'published_at' => '2025-11-24 11:00:00',
        ]);

        // タイトル検索（FULLTEXT検索）
        $result = Article::byTitle('Laravel')->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Laravel フレームワーク入門', $result->first()->title);
    }

    /**
     * 公開日時の範囲検索（以降）のScopeメソッドをテスト
     */
    public function test_scope_published_from(): void
    {
        // テストデータ作成
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => '記事1',
            'content' => '内容1',
            'published_at' => '2025-11-20 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => '記事2',
            'content' => '内容2',
            'published_at' => '2025-11-25 10:00:00',
        ]);

        // 2025/11/23以降の記事を検索
        $result = Article::publishedFrom('2025/11/23')->get();

        $this->assertCount(1, $result);
        $this->assertEquals('記事2', $result->first()->title);
    }

    /**
     * 公開日時の範囲検索（以前）のScopeメソッドをテスト
     */
    public function test_scope_published_to(): void
    {
        // テストデータ作成
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => '記事1',
            'content' => '内容1',
            'published_at' => '2025-11-20 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => '記事2',
            'content' => '内容2',
            'published_at' => '2025-11-25 10:00:00',
        ]);

        // 2025/11/23以前の記事を検索
        $result = Article::publishedTo('2025/11/23')->get();

        $this->assertCount(1, $result);
        $this->assertEquals('記事1', $result->first()->title);
    }

    /**
     * 新しい記事順にソートするScopeメソッドをテスト
     */
    public function test_scope_newest(): void
    {
        // テストデータ作成
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => '記事1',
            'content' => '内容1',
            'published_at' => '2025-11-20 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => '記事2',
            'content' => '内容2',
            'published_at' => '2025-11-25 10:00:00',
        ]);

        // 新しい順に取得
        $results = Article::newest()->get();

        $this->assertEquals('記事2', $results->first()->title);
        $this->assertEquals('記事1', $results->last()->title);
    }

    /**
     * 複数の検索条件を組み合わせてテスト
     */
    public function test_combined_scopes(): void
    {
        // テストデータ作成
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'Laravel 記事1',
            'content' => '内容1',
            'published_at' => '2025-11-20 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => 'Laravel 記事2',
            'content' => '内容2',
            'published_at' => '2025-11-25 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/3',
            'title' => 'PHP 記事3',
            'content' => '内容3',
            'published_at' => '2025-11-25 10:00:00',
        ]);

        // タイトル + 日付範囲 + ソートの組み合わせ
        $results = Article::byTitle('Laravel')
            ->publishedFrom('2025/11/23')
            ->newest()
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Laravel 記事2', $results->first()->title);
    }
}
