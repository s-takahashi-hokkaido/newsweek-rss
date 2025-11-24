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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('url', 255)->unique()->comment('記事URL');
            $table->string('title', 255)->comment('記事タイトル');
            $table->text('content')->comment('記事の内容');
            $table->dateTime('published_at')->comment('記事公開日時');
            $table->timestamps();

            // インデックス
            $table->index('published_at', 'idx_published_at');
            $table->fullText('title', 'idx_title');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
