<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Session;

/**
 * 記事検索サービス
 * 
 * 検索ロジックとセッション管理を担当
 */
class ArticleSearchService
{
    /**
     * 記事を検索する
     *
     * @param array $params 検索条件
     * @return LengthAwarePaginator
     */
    public function search(array $params): LengthAwarePaginator
    {
        $query = Article::query()
            ->byUrl($params['url'] ?? null)
            ->byTitle($params['title'] ?? null)
            ->publishedFrom($params['date_from'] ?? null)
            ->publishedTo($params['date_to'] ?? null)
            ->newest();
        
        $perPage = config('search.per_page', 20);
        
        return $query->paginate($perPage);
    }

    /**
     * 検索条件をセッションに保存する
     *
     * @param array $conditions 検索条件
     * @return void
     */
    public function saveConditions(array $conditions): void
    {
        $sessionKey = config('search.session_key', 'article_search_conditions');
        
        // 空の値は保存しない
        $filtered = array_filter($conditions, function ($value) {
            return !is_null($value) && $value !== '';
        });
        
        Session::put($sessionKey, $filtered);
    }

    /**
     * セッションから検索条件を読み込む
     *
     * @return array
     */
    public function loadConditions(): array
    {
        $sessionKey = config('search.session_key', 'article_search_conditions');
        
        return Session::get($sessionKey, []);
    }

    /**
     * セッションの検索条件をクリアする
     *
     * @return void
     */
    public function clearConditions(): void
    {
        $sessionKey = config('search.session_key', 'article_search_conditions');
        
        Session::forget($sessionKey);
    }
}

