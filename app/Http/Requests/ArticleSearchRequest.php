<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 記事検索リクエスト
 * 
 * 検索条件のバリデーションを担当
 */
class ArticleSearchRequest extends FormRequest
{
    /**
     * リクエストの認可
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'url' => ['nullable', 'url', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * バリデーション属性名（日本語）
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'date_from' => '開始日',
            'date_to' => '終了日',
            'url' => 'URL',
            'title' => 'タイトル',
        ];
    }
}

