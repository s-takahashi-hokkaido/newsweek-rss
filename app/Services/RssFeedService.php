<?php

namespace App\Services;

use App\Models\Article;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

/**
 * RSSフィード取得サービス
 * 
 * ニューズウィーク日本版のRSSフィードから記事を取得し、データベースに保存する
 */
class RssFeedService
{
    /**
     * RSSフィードを取得して記事を保存
     *
     * @return array{success: bool, count: int, error: string|null}
     */
    public function fetch(): array
    {
        $startTime = microtime(true);
        
        try {
            // 1. RSSフィードをHTTP取得（リトライ機能付き）
            $xmlContent = $this->fetchRssXml();
            
            // 2. XMLをパース
            $items = $this->parseXml($xmlContent);
            
            // 3. データベースに保存
            $savedCount = $this->saveArticles($items);
            
            // 4. 成功ログ記録
            $duration = round(microtime(true) - $startTime, 2);
            Log::info('RSS取得成功', [
                'saved_count' => $savedCount,
                'total_items' => count($items),
                'skipped_count' => count($items) - $savedCount,
                'duration' => $duration . '秒',
            ]);
            
            return [
                'success' => true,
                'count' => $savedCount,
                'error' => null,
            ];
            
        } catch (\Exception $e) {
            // 失敗ログ記録
            Log::error('RSS取得失敗', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * RSSフィードをHTTP取得（リトライ機能付き）
     *
     * @return string XML文字列
     * @throws \Exception
     */
    private function fetchRssXml(): string
    {
        $url = config('rss.url');
        $maxRetries = config('rss.retry_count');
        $retryDelay = config('rss.retry_delay');
        
        $client = new Client([
            'timeout' => config('rss.timeout'),
            'headers' => [
                'User-Agent' => config('rss.user_agent'),
            ],
        ]);
        
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = $client->get($url);
                return (string) $response->getBody();
                
            } catch (RequestException $e) {
                $lastException = $e;
                
                // 最終試行でなければリトライ
                if ($attempt < $maxRetries) {
                    Log::warning('RSS取得リトライ', [
                        'attempt' => $attempt,
                        'max_retries' => $maxRetries,
                        'error' => $e->getMessage(),
                    ]);
                    sleep($retryDelay);
                }
            }
        }
        
        // 全てのリトライが失敗
        throw new \Exception(
            "RSS取得に失敗しました（{$maxRetries}回リトライ）: " . $lastException->getMessage(),
            0,
            $lastException
        );
    }
    
    /**
     * XMLをパースして記事データの配列を返す
     *
     * @param string $xmlContent XML文字列
     * @return array<int, array{url: string, title: string, content: string, published_at: string}>
     * @throws \Exception
     */
    private function parseXml(string $xmlContent): array
    {
        try {
            // libxml エラーを内部で処理
            libxml_use_internal_errors(true);
            
            $xml = new SimpleXMLElement($xmlContent);
            
            // libxml エラーをチェック
            $errors = libxml_get_errors();
            if (!empty($errors)) {
                libxml_clear_errors();
                throw new \Exception('XML形式が不正です');
            }
            
        } catch (\Exception $e) {
            throw new \Exception('XMLパースに失敗しました: ' . $e->getMessage(), 0, $e);
        }
        
        $items = [];
        
        foreach ($xml->channel->item as $item) {
            try {
                // 必須項目のチェック
                if (empty($item->link) || empty($item->title) || empty($item->pubDate)) {
                    Log::warning('RSS記事に必須項目が不足しているためスキップ', [
                        'link' => (string) $item->link ?? 'null',
                        'title' => (string) $item->title ?? 'null',
                    ]);
                    continue;
                }
                
                $items[] = [
                    'url' => (string) $item->link,
                    'title' => (string) $item->title,
                    'content' => (string) $item->description,
                    'published_at' => $this->convertPubDate((string) $item->pubDate),
                ];
                
            } catch (\Exception $e) {
                // 個別の記事でエラーが発生してもスキップして続行
                Log::warning('RSS記事のパースに失敗（スキップ）', [
                    'link' => (string) $item->link ?? 'null',
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        if (empty($items)) {
            throw new \Exception('RSSフィードに有効な記事が見つかりませんでした');
        }
        
        return $items;
    }
    
    /**
     * pubDateをMySQL DATETIME形式に変換
     *
     * @param string $pubDate RFC2822形式の日付（例: "Mon, 25 Nov 2025 10:30:00 +0900"）
     * @return string MySQL DATETIME形式（例: "2025-11-25 10:30:00"）
     * @throws \Exception
     */
    private function convertPubDate(string $pubDate): string
    {
        try {
            // RFC2822形式からCarbonインスタンスを作成
            $carbon = Carbon::createFromFormat('D, d M Y H:i:s O', $pubDate);
            
            // MySQL DATETIME形式で返す
            return $carbon->format('Y-m-d H:i:s');
            
        } catch (\Exception $e) {
            throw new \Exception(
                "日付変換に失敗しました（pubDate: {$pubDate}）: " . $e->getMessage(),
                0,
                $e
            );
        }
    }
    
    /**
     * 記事をデータベースに保存
     *
     * @param array<int, array{url: string, title: string, content: string, published_at: string}> $items
     * @return int 保存した新規記事数
     */
    private function saveArticles(array $items): int
    {
        $savedCount = 0;
        
        foreach ($items as $item) {
            try {
                // firstOrCreate: URLが既に存在する場合は作成しない
                $article = Article::firstOrCreate(
                    ['url' => $item['url']], // 検索条件
                    $item                     // 作成データ
                );
                
                // 新規作成された場合のみカウント
                if ($article->wasRecentlyCreated) {
                    $savedCount++;
                }
                
            } catch (\Exception $e) {
                // 個別の記事でエラーが発生してもスキップして続行
                Log::warning('記事の保存に失敗（スキップ）', [
                    'url' => $item['url'],
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return $savedCount;
    }
}

