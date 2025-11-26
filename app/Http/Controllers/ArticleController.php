<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleSearchRequest;
use App\Models\RssFetchLog;
use App\Services\ArticleSearchService;
use Illuminate\Contracts\View\View;

/**
 * 記事コントローラー
 * 
 * 記事検索画面の表示と検索処理を担当
 */
class ArticleController extends Controller
{
    /**
     * @var ArticleSearchService
     */
    private ArticleSearchService $searchService;

    /**
     * コンストラクタ
     *
     * @param ArticleSearchService $searchService
     */
    public function __construct(ArticleSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * 記事検索画面の表示と検索実行
     *
     * @param ArticleSearchRequest $request
     * @return View
     */
    public function index(ArticleSearchRequest $request): View
    {
        // 検索条件の取得（リクエスト or セッション）
        $conditions = $this->getSearchConditions($request);
        
        // 検索実行
        $articles = $this->searchService->search($conditions);
        
        // 検索条件をセッションに保存（次回訪問時に復元）
        if ($request->hasAny(['date_from', 'date_to', 'url', 'title'])) {
            $this->searchService->saveConditions($conditions);
        }
        
        // RSS取得状況の取得
        $rssStatus = $this->getRssFetchStatus();
        
        return view('articles.index', [
            'articles' => $articles,
            'conditions' => $conditions,
            'latest_fetch' => $rssStatus['latest_fetch'],
            'latest_success' => $rssStatus['latest_success'],
            'is_fetch_healthy' => $rssStatus['is_fetch_healthy'],
        ]);
    }

    /**
     * 検索条件の取得
     * 
     * リクエストに検索パラメータがあればそれを使用、
     * なければセッションから復元
     *
     * @param ArticleSearchRequest $request
     * @return array
     */
    private function getSearchConditions(ArticleSearchRequest $request): array
    {
        // リクエストに検索パラメータがあるか確認
        if ($request->hasAny(['date_from', 'date_to', 'url', 'title'])) {
            return $request->only(['date_from', 'date_to', 'url', 'title']);
        }
        
        // なければセッションから復元
        return $this->searchService->loadConditions();
    }

    /**
     * RSS取得状況の取得
     *
     * @return array{latest_fetch: RssFetchLog|null, latest_success: RssFetchLog|null, is_fetch_healthy: bool}
     */
    private function getRssFetchStatus(): array
    {
        $latestFetch = RssFetchLog::latest();
        $latestSuccess = RssFetchLog::latestSuccess();
        
        return [
            'latest_fetch' => $latestFetch,
            'latest_success' => $latestSuccess,
            'is_fetch_healthy' => $latestFetch && $latestFetch->isSuccess(),
        ];
    }
}

