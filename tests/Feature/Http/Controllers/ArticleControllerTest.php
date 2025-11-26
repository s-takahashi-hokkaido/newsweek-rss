<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Article;
use App\Models\RssFetchLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 検索画面が正常に表示されることをテスト
     */
    public function test_index_page_displays_successfully(): void
    {
        $response = $this->get(route('articles.index'));

        $response->assertStatus(200);
        $response->assertViewIs('articles.index');
        $response->assertViewHas(['articles', 'conditions', 'latest_fetch', 'latest_success', 'is_fetch_healthy']);
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

        $response = $this->get(route('articles.index', ['url' => 'https://example.com/article/1']));

        $response->assertStatus(200);
        $response->assertSee('テスト記事1');
        $response->assertDontSee('テスト記事2');
        $response->assertSeeInOrder(['1', '件']); // 検索結果件数
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

        $response = $this->get(route('articles.index', ['title' => 'Laravel']));

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
        $response->assertDontSee('PHP基礎');
    }

    /**
     * 日付範囲検索が正しく動作することをテスト
     */
    public function test_search_by_date_range(): void
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
            'published_at' => '2025-11-30 12:00:00',
        ]);

        $response = $this->get(route('articles.index', [
            'date_from' => '2025-11-23',
            'date_to' => '2025-11-27',
        ]));

        $response->assertStatus(200);
        $response->assertSee('テスト記事2');
        $response->assertDontSee('テスト記事1');
        $response->assertDontSee('テスト記事3');
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

        $response = $this->get(route('articles.index', [
            'title' => 'Laravel',
            'date_from' => '2025-11-23',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Laravel応用');
        $response->assertDontSee('Laravel入門');
        $response->assertDontSee('PHP基礎');
    }

    /**
     * 検索条件なしで全件表示されることをテスト
     */
    public function test_displays_all_articles_without_search_conditions(): void
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

        $response = $this->get(route('articles.index'));

        $response->assertStatus(200);
        $response->assertSee('テスト記事1');
        $response->assertSee('テスト記事2');
        $response->assertSeeInOrder(['2', '件']);
    }

    /**
     * 検索結果が新しい順に表示されることをテスト
     */
    public function test_articles_are_displayed_in_newest_order(): void
    {
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => '古い記事',
            'content' => 'テスト内容1',
            'published_at' => '2025-11-20 10:00:00',
        ]);

        Article::create([
            'url' => 'https://example.com/article/2',
            'title' => '新しい記事',
            'content' => 'テスト内容2',
            'published_at' => '2025-11-25 11:00:00',
        ]);

        $response = $this->get(route('articles.index'));

        $response->assertStatus(200);
        
        // レスポンスの順序を確認
        $content = $response->getContent();
        $posNew = strpos($content, '新しい記事');
        $posOld = strpos($content, '古い記事');
        
        $this->assertLessThan($posOld, $posNew, '新しい記事が先に表示されるべき');
    }

    /**
     * ページネーションが正しく動作することをテスト
     */
    public function test_pagination_works_correctly(): void
    {
        // 25件の記事を作成（1ページ20件なので2ページ目が存在する）
        for ($i = 1; $i <= 25; $i++) {
            Article::create([
                'url' => "https://example.com/article/{$i}",
                'title' => "テスト記事{$i}",
                'content' => "テスト内容{$i}",
                'published_at' => now()->subMinutes(25 - $i), // 新しい順
            ]);
        }

        // 1ページ目
        $response = $this->get(route('articles.index'));
        $response->assertStatus(200);
        $response->assertSeeInOrder(['25', '件']); // 全件数
        $response->assertSee('テスト記事25'); // 最新
        $response->assertSee('テスト記事6');
        $response->assertDontSee('テスト記事5'); // 21件目以降は2ページ目

        // 2ページ目
        $response = $this->get(route('articles.index', ['page' => 2]));
        $response->assertStatus(200);
        $response->assertSee('テスト記事5');
        $response->assertSee('テスト記事1');
        $response->assertDontSee('テスト記事6'); // 1ページ目の記事
    }

    /**
     * 検索条件がセッションに保存されることをテスト
     */
    public function test_search_conditions_are_saved_to_session(): void
    {
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'テスト記事',
            'content' => 'テスト内容',
            'published_at' => '2025-11-25 10:00:00',
        ]);

        $response = $this->get(route('articles.index', [
            'date_from' => '2025-11-01',
            'title' => 'テスト',
        ]));

        $response->assertSessionHas('article_search_conditions');
        
        $conditions = session('article_search_conditions');
        $this->assertEquals('2025-11-01', $conditions['date_from']);
        $this->assertEquals('テスト', $conditions['title']);
    }

    /**
     * セッションから検索条件が復元されることをテスト
     */
    public function test_search_conditions_are_restored_from_session(): void
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

        // 1回目のリクエストで検索条件を保存
        $this->get(route('articles.index', ['title' => 'Laravel']));

        // 2回目のリクエスト（検索条件なし）でセッションから復元されることを確認
        $response = $this->get(route('articles.index'));

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
        $response->assertDontSee('PHP基礎');
    }

    /**
     * バリデーションエラーが正しく表示されることをテスト
     */
    public function test_validation_errors_are_displayed(): void
    {
        $response = $this->get(route('articles.index', [
            'date_from' => '2025/11/30', // 不正な形式（スラッシュ）
        ]));

        $response->assertStatus(302); // リダイレクト
        $response->assertSessionHasErrors('date_from');
    }

    /**
     * RSS取得状況（成功）が正しく表示されることをテスト
     */
    public function test_displays_rss_fetch_status_success(): void
    {
        RssFetchLog::create([
            'fetched_at' => now(),
            'status' => RssFetchLog::STATUS_SUCCESS,
            'articles_count' => 10,
            'error_message' => null,
        ]);

        $response = $this->get(route('articles.index'));

        $response->assertStatus(200);
        $response->assertSee('正常に取得されました');
        $response->assertDontSee('取得に失敗しました');
    }

    /**
     * RSS取得状況（失敗）が正しく表示されることをテスト
     */
    public function test_displays_rss_fetch_status_failure(): void
    {
        RssFetchLog::create([
            'fetched_at' => now(),
            'status' => RssFetchLog::STATUS_FAILURE,
            'articles_count' => 0,
            'error_message' => 'ネットワークエラー',
        ]);

        $response = $this->get(route('articles.index'));

        $response->assertStatus(200);
        $response->assertSee('取得に失敗しました');
        $response->assertSee('ネットワークエラー');
    }

    /**
     * RSS取得履歴がない場合の表示をテスト
     */
    public function test_displays_no_rss_fetch_history_message(): void
    {
        $response = $this->get(route('articles.index'));

        $response->assertStatus(200);
        $response->assertSee('まだRSSが取得されていません');
        $response->assertSee('php artisan rss:fetch');
    }

    /**
     * 検索結果が0件の場合のメッセージをテスト
     */
    public function test_displays_no_results_message(): void
    {
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => 'テスト記事',
            'content' => 'テスト内容',
            'published_at' => '2025-11-25 10:00:00',
        ]);

        $response = $this->get(route('articles.index', ['title' => '存在しないキーワード']));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['0', '件']);
        $response->assertSee('検索条件に一致する記事が見つかりませんでした');
    }
}

