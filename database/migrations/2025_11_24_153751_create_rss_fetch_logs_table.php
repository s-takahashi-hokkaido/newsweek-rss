<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rss_fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fetched_at')->comment('RSS取得実行日時');
            $table->enum('status', ['success', 'failure'])->comment('取得成否');
            $table->unsignedInteger('articles_count')->nullable()->comment('取得した記事数');
            $table->text('error_message')->nullable()->comment('エラーメッセージ');
            $table->timestamp('created_at')->useCurrent()->comment('レコード作成日時');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rss_fetch_logs');
    }
};
