<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Services\ArticleSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ArticleSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    private ArticleSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ArticleSearchService();
    }

    /**
     * URL検索が正しく動作することをテスト
     */
    public function test_search_by_url(): void
    {
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'テスト記事1',
            'content' => 'テスト内容1',
            'published_at' => '2025-11-25 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => 'テスト記事2',
            'content' => 'テスト内容2',
            'published_at' => '2025-11-25 11:00:00',
        ]);

        $result = $this->service->search(['url' => 'https://example.com/article/1']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('https://example.com/article/1', $result->first()->url);
    }

    /**
     * タイトル検索が正しく動作することをテスト
     */
    public function test_search_by_title(): void
    {
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'Laravel入門',
            'content' => 'テスト内容1',
            'published_at' => '2025-11-25 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => 'PHP基礎',
            'content' => 'テスト内容2',
            'published_at' => '2025-11-25 11:00:00',
        ]);

        $result = $this->service->search(['title' => 'Laravel']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Laravel入門', $result->first()->title);
    }

    /**
     * 日付範囲検索（開始日）が正しく動作することをテスト
     */
    public function test_search_by_date_from(): void
    {
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'テスト記事1',
            'content' => 'テスト内容1',
            'published_at' => '2025-11-20 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => 'テスト記事2',
            'content' => 'テスト内容2',
            'published_at' => '2025-11-25 11:00:00',
        ]);

        $result = $this->service->search(['date_from' => '2025-11-25']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('テスト記事2', $result->first()->title);
    }

    /**
     * 日付範囲検索（終了日）が正しく動作することをテスト
     */
    public function test_search_by_date_to(): void
    {
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'テスト記事1',
            'content' => 'テスト内容1',
            'published_at' => '2025-11-20 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => 'テスト記事2',
            'content' => 'テスト内容2',
            'published_at' => '2025-11-25 11:00:00',
        ]);

        $result = $this->service->search(['date_to' => '2025-11-22']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('テスト記事1', $result->first()->title);
    }

    /**
     * 日付範囲検索（開始日〜終了日）が正しく動作することをテスト
     */
    public function test_search_by_date_range(): void
    {
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'テスト記事1',
            'content' => 'テスト内容1',
            'published_at' => '2025-11-10 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => 'テスト記事2',
            'content' => 'テスト内容2',
            'published_at' => '2025-11-20 11:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/3',
            'title' => 'テスト記事3',
            'content' => 'テスト内容3',
            'published_at' => '2025-11-30 12:00:00',
        ]);

        $result = $this->service->search([
            'date_from' => '2025-11-15',
            'date_to' => '2025-11-25',
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('テスト記事2', $result->first()->title);
    }

    /**
     * 複合条件検索が正しく動作することをテスト
     */
    public function test_search_with_multiple_conditions(): void
    {
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'Laravel入門',
            'content' => 'テスト内容1',
            'published_at' => '2025-11-20 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => 'Laravel応用',
            'content' => 'テスト内容2',
            'published_at' => '2025-11-25 11:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/3',
            'title' => 'PHP基礎',
            'content' => 'テスト内容3',
            'published_at' => '2025-11-25 12:00:00',
        ]);

        $result = $this->service->search([
            'title' => 'Laravel',
            'date_from' => '2025-11-23',
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Laravel応用', $result->first()->title);
    }

    /**
     * 空の検索条件で全件取得できることをテスト
     */
    public function test_search_with_empty_conditions_returns_all(): void
    {
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'テスト記事1',
            'content' => 'テスト内容1',
            'published_at' => '2025-11-25 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => 'テスト記事2',
            'content' => 'テスト内容2',
            'published_at' => '2025-11-25 11:00:00',
        ]);

        $result = $this->service->search([]);

        $this->assertEquals(2, $result->total());
    }

    /**
     * ページネーション件数が設定ファイル通りに動作することをテスト
     */
    public function test_pagination_per_page_from_config(): void
    {
        // 25件の記事を作成
        for ($i = 1; $i <= 25; $i++) {
            Article::create([
                'url' => "https://example.com/article/{$i}",
                'title' => "テスト記事{$i}",
                'content' => "テスト内容{$i}",
                'published_at' => '2025-11-25 10:00:00',
            ]);
        }

        $result = $this->service->search([]);

        // config/search.phpのper_pageは20
        $this->assertEquals(25, $result->total());
        $this->assertEquals(20, $result->perPage());
        $this->assertEquals(20, $result->count()); // 現在のページに20件
    }

    /**
     * 検索結果が新しい順にソートされることをテスト
     */
    public function test_search_results_are_sorted_by_newest(): void
    {
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'テスト記事1',
            'content' => 'テスト内容1',
            'published_at' => '2025-11-20 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => 'テスト記事2',
            'content' => 'テスト内容2',
            'published_at' => '2025-11-25 11:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/3',
            'title' => 'テスト記事3',
            'content' => 'テスト内容3',
            'published_at' => '2025-11-22 12:00:00',
        ]);

        $result = $this->service->search([]);

        $articles = $result->items();
        $this->assertEquals('テスト記事2', $articles[0]->title); // 最新
        $this->assertEquals('テスト記事3', $articles[1]->title);
        $this->assertEquals('テスト記事1', $articles[2]->title); // 最古
    }

    /**
     * 検索条件をセッションに保存できることをテスト
     */
    public function test_save_conditions_to_session(): void
    {
        $conditions = [
            'date_from' => '2025-11-01',
            'date_to' => '2025-11-30',
            'url' => 'https://example.com/article',
            'title' => 'Laravel',
        ];

        $this->service->saveConditions($conditions);

        $this->assertEquals($conditions, Session::get('article_search_conditions'));
    }

    /**
     * 空の値はセッションに保存されないことをテスト
     */
    public function test_save_conditions_filters_empty_values(): void
    {
        $conditions = [
            'date_from' => '2025-11-01',
            'date_to' => null,
            'url' => '',
            'title' => 'Laravel',
        ];

        $this->service->saveConditions($conditions);

        $saved = Session::get('article_search_conditions');
        $this->assertArrayHasKey('date_from', $saved);
        $this->assertArrayHasKey('title', $saved);
        $this->assertArrayNotHasKey('date_to', $saved);
        $this->assertArrayNotHasKey('url', $saved);
    }

    /**
     * セッションから検索条件を読み込めることをテスト
     */
    public function test_load_conditions_from_session(): void
    {
        $conditions = [
            'date_from' => '2025-11-01',
            'title' => 'Laravel',
        ];

        Session::put('article_search_conditions', $conditions);

        $loaded = $this->service->loadConditions();

        $this->assertEquals($conditions, $loaded);
    }

    /**
     * セッションに検索条件がない場合は空配列を返すことをテスト
     */
    public function test_load_conditions_returns_empty_array_when_not_exists(): void
    {
        $loaded = $this->service->loadConditions();

        $this->assertEquals([], $loaded);
    }

    /**
     * セッションの検索条件をクリアできることをテスト
     */
    public function test_clear_conditions_from_session(): void
    {
        $conditions = [
            'date_from' => '2025-11-01',
            'title' => 'Laravel',
        ];

        Session::put('article_search_conditions', $conditions);
        $this->assertTrue(Session::has('article_search_conditions'));

        $this->service->clearConditions();

        $this->assertFalse(Session::has('article_search_conditions'));
    }
}

