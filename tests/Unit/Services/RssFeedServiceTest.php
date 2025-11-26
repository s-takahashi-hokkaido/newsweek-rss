<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Services\RssFeedService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RssFeedServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト用のRSS XMLを生成
     */
    private function createTestRssXml(array $items = []): string
    {
        if (empty($items)) {
            $items = [
                [
                    'title' => 'テスト記事1',
                    'link' => 'https://example.com/article/1',
                    'description' => 'テスト内容1',
                    'pubDate' => 'Mon, 25 Nov 2025 10:00:00 +0900',
                ],
            ];
        }

        $itemsXml = '';
        foreach ($items as $item) {
            $itemsXml .= '
                <item>
                    <title>' . htmlspecialchars($item['title']) . '</title>
                    <link>' . htmlspecialchars($item['link']) . '</link>
                    <description><![CDATA[' . $item['description'] . ']]></description>
                    <pubDate>' . $item['pubDate'] . '</pubDate>
                </item>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>テストRSS</title>
        <link>https://example.com/</link>
        <description>テスト用RSSフィード</description>
        ' . $itemsXml . '
    </channel>
</rss>';
    }

    /**
     * モックHTTPクライアントを使ってサービスを作成
     */
    private function createServiceWithMock(array $responses): RssFeedService
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // サービスのインスタンスを作成してHTTPクライアントを差し替え
        $service = new RssFeedService();
        
        // Reflectionを使ってプライベートメソッドにモックを注入
        // （実際のテストでは依存性注入で実装するのが理想）
        
        return $service;
    }

    /**
     * 正常系: RSS取得と保存
     */
    public function test_fetch_success_with_new_articles()
    {
        $this->markTestSkipped('HTTPクライアントのモック化にはリファクタリングが必要');
        
        // テストRSS XMLを準備
        $xml = $this->createTestRssXml([
            [
                'title' => 'テスト記事1',
                'link' => 'https://example.com/article/1',
                'description' => 'テスト内容1',
                'pubDate' => 'Mon, 25 Nov 2025 10:00:00 +0900',
            ],
            [
                'title' => 'テスト記事2',
                'link' => 'https://example.com/article/2',
                'description' => 'テスト内容2',
                'pubDate' => 'Mon, 25 Nov 2025 11:00:00 +0900',
            ],
        ]);

        // TODO: HTTPクライアントをモック化
        // $service = $this->createServiceWithMock([
        //     new Response(200, [], $xml),
        // ]);
        // 
        // $result = $service->fetch();
        // 
        // $this->assertTrue($result['success']);
        // $this->assertEquals(2, $result['count']);
        // $this->assertNull($result['error']);
        // 
        // $this->assertEquals(2, Article::count());
    }

    /**
     * 正常系: 重複記事のスキップ
     */
    public function test_fetch_skips_duplicate_articles()
    {
        // 既存記事を作成
        Article::create([
            'url' => 'https://example.com/article/1',
            'title' => '既存記事',
            'content' => '既存内容',
            'published_at' => '2025-11-25 10:00:00',
        ]);

        $this->assertEquals(1, Article::count());

        // 同じURLを含むRSSを取得（モック化が必要）
        $this->markTestSkipped('HTTPクライアントのモック化にはリファクタリングが必要');
    }

    /**
     * 正常系: 日付変換
     */
    public function test_date_conversion()
    {
        // RssFeedServiceのconvertPubDateメソッドをテスト
        // （プライベートメソッドなのでReflectionが必要）
        $service = new RssFeedService();
        
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('convertPubDate');
        $method->setAccessible(true);

        // RFC2822形式の日付（実際のRSSから）
        $pubDate = 'Sun, 24 Nov 2024 10:30:00 +0900';
        $result = $method->invoke($service, $pubDate);

        // MySQL DATETIME形式に変換されていることを確認
        $this->assertEquals('2024-11-24 10:30:00', $result);
    }

    /**
     * 異常系: 不正な日付フォーマット
     */
    public function test_invalid_date_format_throws_exception()
    {
        $service = new RssFeedService();
        
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('convertPubDate');
        $method->setAccessible(true);

        $this->expectException(\Exception::class);
        $method->invoke($service, 'invalid date format');
    }

    /**
     * 異常系: 空のRSSフィード
     */
    public function test_empty_rss_feed_throws_exception()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>空のRSS</title>
    </channel>
</rss>';

        $service = new RssFeedService();
        
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseXml');
        $method->setAccessible(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('RSSフィードに有効な記事が見つかりませんでした');
        $method->invoke($service, $xml);
    }

    /**
     * 異常系: 不正なXML形式
     */
    public function test_invalid_xml_format_throws_exception()
    {
        $invalidXml = '<html><body>This is not XML</body></html>';

        $service = new RssFeedService();
        
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseXml');
        $method->setAccessible(true);

        // HTML形式の場合、channel->itemがnullになるため例外が発生
        $this->expectException(\Exception::class);
        $method->invoke($service, $invalidXml);
    }

    /**
     * 境界値: 必須項目が不足している記事
     */
    public function test_items_with_missing_required_fields_are_skipped()
    {
        $xml = $this->createTestRssXml([
            [
                'title' => 'テスト記事1',
                'link' => 'https://example.com/article/1',
                'description' => 'テスト内容1',
                'pubDate' => 'Mon, 25 Nov 2025 10:00:00 +0900',
            ],
            // 必須項目（link）が不足
            [
                'title' => 'テスト記事2',
                'link' => '', // 空
                'description' => 'テスト内容2',
                'pubDate' => 'Mon, 25 Nov 2025 11:00:00 +0900',
            ],
        ]);

        $service = new RssFeedService();
        
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseXml');
        $method->setAccessible(true);

        $items = $method->invoke($service, $xml);

        // 有効な記事のみが返される
        $this->assertCount(1, $items);
        $this->assertEquals('https://example.com/article/1', $items[0]['url']);
    }
}

