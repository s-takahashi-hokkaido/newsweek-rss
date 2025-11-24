<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 記事モデル
 * 
 * ニューズウィーク日本版のRSSから取得した記事を管理
 */
class Article extends Model
{
    use HasFactory;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'articles';

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'url',
        'title',
        'content',
        'published_at',
    ];

    /**
     * 属性のキャスト
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * URL完全一致検索
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $url
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUrl($query, ?string $url)
    {
        if (empty($url)) {
            return $query;
        }

        return $query->where('url', $url);
    }

    /**
     * タイトル部分一致検索（FULLTEXT INDEX使用）
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $title
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTitle($query, ?string $title)
    {
        if (empty($title)) {
            return $query;
        }

        // 本番環境ではFULLTEXT INDEXを使用した全文検索
        // テスト環境ではLIKE検索を使用
        if (config('app.env') === 'testing') {
            return $query->where('title', 'like', '%' . $title . '%');
        }

        // FULLTEXT INDEXを使用した全文検索（100万件のデータでも高速）
        return $query->whereRaw(
            'MATCH(title) AGAINST(? IN BOOLEAN MODE)',
            [$title]
        );
    }

    /**
     * 公開日時の範囲検索（指定日以降）
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dateFrom yyyy/mm/dd形式
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublishedFrom($query, ?string $dateFrom)
    {
        if (empty($dateFrom)) {
            return $query;
        }

        return $query->where('published_at', '>=', $dateFrom . ' 00:00:00');
    }

    /**
     * 公開日時の範囲検索（指定日以前）
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dateTo yyyy/mm/dd形式
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublishedTo($query, ?string $dateTo)
    {
        if (empty($dateTo)) {
            return $query;
        }

        return $query->where('published_at', '<=', $dateTo . ' 23:59:59');
    }

    /**
     * 新しい記事順にソート
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }
}

