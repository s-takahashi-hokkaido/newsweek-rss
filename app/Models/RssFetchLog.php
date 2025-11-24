<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * RSS取得ログモデル
 * 
 * RSSの取得履歴と取得状況を記録
 */
class RssFetchLog extends Model
{
    use HasFactory;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'rss_fetch_logs';

    /**
     * updated_atカラムを使用しない
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fetched_at',
        'status',
        'articles_count',
        'error_message',
    ];

    /**
     * 属性のキャスト
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fetched_at' => 'datetime',
        'created_at' => 'datetime',
        'articles_count' => 'integer',
    ];

    /**
     * ステータス定数
     */
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILURE = 'failure';

    /**
     * 最新のログを取得
     *
     * @return self|null
     */
    public static function latest()
    {
        return self::orderBy('id', 'desc')->first();
    }

    /**
     * 最新の成功ログを取得
     *
     * @return self|null
     */
    public static function latestSuccess()
    {
        return self::where('status', self::STATUS_SUCCESS)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * 成功かどうかを判定
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * 失敗かどうかを判定
     *
     * @return bool
     */
    public function isFailure(): bool
    {
        return $this->status === self::STATUS_FAILURE;
    }

    /**
     * 成功ログのみに絞り込み
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * 失敗ログのみに絞り込み
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailure($query)
    {
        return $query->where('status', self::STATUS_FAILURE);
    }
}

