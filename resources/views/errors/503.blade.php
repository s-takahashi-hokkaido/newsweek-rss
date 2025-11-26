@extends('layouts.app')

@section('title', 'メンテナンス中 - ニューズウィーク日本版 記事検索')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="max-w-2xl w-full text-center">
        <!-- エラーコード -->
        <div class="mb-8">
            <h1 class="text-8xl font-bold text-yellow-600 mb-4">503</h1>
            <div class="h-1 w-24 bg-yellow-600 mx-auto rounded"></div>
        </div>

        <!-- メッセージ -->
        <div class="mb-8">
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">
                メンテナンス中
            </h2>
            <p class="text-gray-600 text-lg leading-relaxed">
                現在、システムメンテナンスを実施しております。<br>
                ご不便をおかけして申し訳ございません。
            </p>
        </div>

        <!-- アクション -->
        <div class="space-y-4">
            <button onclick="location.reload()" 
                    class="inline-flex items-center justify-center px-8 py-4 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 transition-colors duration-200 shadow-md hover:shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                ページを再読み込み
            </button>
        </div>

        <!-- 補足情報 -->
        <div class="mt-12 p-6 bg-blue-50 rounded-lg border border-blue-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                メンテナンスについて
            </h3>
            <p class="text-gray-700 text-sm">
                メンテナンス作業は通常30分〜1時間程度で完了します。<br>
                しばらく時間をおいてから再度アクセスしてください。
            </p>
        </div>
    </div>
</div>
@endsection




