@extends('layouts.app')

@section('title', 'ページが見つかりません - ニューズウィーク日本版 記事検索')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="max-w-2xl w-full text-center">
        <!-- エラーコード -->
        <div class="mb-8">
            <h1 class="text-8xl font-bold text-red-600 mb-4">404</h1>
            <div class="h-1 w-24 bg-red-600 mx-auto rounded"></div>
        </div>

        <!-- エラーメッセージ -->
        <div class="mb-8">
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">
                ページが見つかりません
            </h2>
            <p class="text-gray-600 text-lg leading-relaxed">
                お探しのページは存在しないか、移動または削除された可能性があります。<br>
                URLが正しいかご確認ください。
            </p>
        </div>

        <!-- アクション -->
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('articles.index') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    トップページへ戻る
                </a>
                
                <button onclick="history.back()" 
                        class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition-colors duration-200 shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    前のページへ戻る
                </button>
            </div>
        </div>

        <!-- 補足情報 -->
        <div class="mt-12 p-6 bg-blue-50 rounded-lg border border-blue-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                お探しの情報について
            </h3>
            <p class="text-gray-700 text-sm">
                記事は定期的に更新されており、古い記事は3ヶ月で自動的に削除されます。<br>
                最新の記事は<a href="{{ route('articles.index') }}" class="text-blue-600 hover:text-blue-800 underline font-medium">検索ページ</a>からご覧いただけます。
            </p>
        </div>
    </div>
</div>
@endsection




