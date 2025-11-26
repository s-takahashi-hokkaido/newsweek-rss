<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\ArticleSearchRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ArticleSearchRequestTest extends TestCase
{
    /**
     * 正常な入力値（全項目あり）でバリデーションが通ることをテスト
     */
    public function test_validation_passes_with_all_valid_inputs(): void
    {
        $request = new ArticleSearchRequest();
        $validator = Validator::make([
            'date_from' => '2025-11-01',
            'date_to' => '2025-11-30',
            'url' => 'https://example.com/article',
            'title' => 'Laravel',
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * 全項目がnullでもバリデーションが通ることをテスト（nullable）
     */
    public function test_validation_passes_with_all_null_inputs(): void
    {
        $request = new ArticleSearchRequest();
        $validator = Validator::make([
            'date_from' => null,
            'date_to' => null,
            'url' => null,
            'title' => null,
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * 項目が全くない場合もバリデーションが通ることをテスト
     */
    public function test_validation_passes_with_no_inputs(): void
    {
        $request = new ArticleSearchRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * date_fromの形式が不正な場合にバリデーションエラーになることをテスト
     */
    public function test_validation_fails_with_invalid_date_from_format(): void
    {
        $request = new ArticleSearchRequest();
        
        // yyyy/mm/dd形式（ハイフンでない）
        $validator = Validator::make([
            'date_from' => '2025/11/01',
        ], $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date_from', $validator->errors()->toArray());

        // 不正な日付
        $validator = Validator::make([
            'date_from' => 'invalid-date',
        ], $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date_from', $validator->errors()->toArray());
    }

    /**
     * date_toの形式が不正な場合にバリデーションエラーになることをテスト
     */
    public function test_validation_fails_with_invalid_date_to_format(): void
    {
        $request = new ArticleSearchRequest();
        
        // yyyy/mm/dd形式（ハイフンでない）
        $validator = Validator::make([
            'date_to' => '2025/11/30',
        ], $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date_to', $validator->errors()->toArray());
    }

    /**
     * date_toがdate_fromより前の場合にバリデーションエラーになることをテスト
     */
    public function test_validation_fails_when_date_to_is_before_date_from(): void
    {
        $request = new ArticleSearchRequest();
        $validator = Validator::make([
            'date_from' => '2025-11-30',
            'date_to' => '2025-11-01',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date_to', $validator->errors()->toArray());
    }

    /**
     * date_toとdate_fromが同じ日付の場合はバリデーションが通ることをテスト
     */
    public function test_validation_passes_when_date_to_equals_date_from(): void
    {
        $request = new ArticleSearchRequest();
        $validator = Validator::make([
            'date_from' => '2025-11-25',
            'date_to' => '2025-11-25',
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * URLの形式が不正な場合にバリデーションエラーになることをテスト
     */
    public function test_validation_fails_with_invalid_url_format(): void
    {
        $request = new ArticleSearchRequest();
        
        $validator = Validator::make([
            'url' => 'not-a-url',
        ], $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('url', $validator->errors()->toArray());

        $validator = Validator::make([
            'url' => 'example.com',
        ], $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('url', $validator->errors()->toArray());
    }

    /**
     * URLが255文字を超える場合にバリデーションエラーになることをテスト
     */
    public function test_validation_fails_when_url_exceeds_max_length(): void
    {
        $request = new ArticleSearchRequest();
        $validator = Validator::make([
            'url' => 'https://example.com/' . str_repeat('a', 256),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('url', $validator->errors()->toArray());
    }

    /**
     * タイトルが255文字を超える場合にバリデーションエラーになることをテスト
     */
    public function test_validation_fails_when_title_exceeds_max_length(): void
    {
        $request = new ArticleSearchRequest();
        $validator = Validator::make([
            'title' => str_repeat('あ', 256),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /**
     * タイトルが255文字以内の場合はバリデーションが通ることをテスト
     */
    public function test_validation_passes_when_title_is_within_max_length(): void
    {
        $request = new ArticleSearchRequest();
        $validator = Validator::make([
            'title' => str_repeat('あ', 255),
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * authorizeメソッドが常にtrueを返すことをテスト
     */
    public function test_authorize_returns_true(): void
    {
        $request = new ArticleSearchRequest();
        $this->assertTrue($request->authorize());
    }
}

