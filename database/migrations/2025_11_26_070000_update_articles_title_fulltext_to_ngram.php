<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * FULLTEXTインデックスをngram parserを使用するように変更
     * これにより日本語の部分一致検索が可能になる
     */
    public function up(): void
    {
        // 既存のFULLTEXTインデックスを削除
        DB::statement('ALTER TABLE articles DROP INDEX idx_title');

        // ngram parserを使用したFULLTEXTインデックスを作成
        // ngram_token_size=2 がデフォルト（2文字単位でトークン化）
        DB::statement('ALTER TABLE articles ADD FULLTEXT INDEX idx_title (title) WITH PARSER ngram');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ngram付きのFULLTEXTインデックスを削除
        DB::statement('ALTER TABLE articles DROP INDEX idx_title');

        // 通常のFULLTEXTインデックスを再作成
        DB::statement('ALTER TABLE articles ADD FULLTEXT INDEX idx_title (title)');
    }
};
