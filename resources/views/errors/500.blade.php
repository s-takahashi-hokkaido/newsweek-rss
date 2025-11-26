@extends('layouts.app')

@section('title', 'サーバーエラー - ニューズウィーク日本版 記事検索')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="max-w-2xl w-full text-center">
        <!-- エラーコード -->
        <div class="mb-8">
            <h1 class="text-8xl font-bold text-red-600 mb-4">500</h1>
            <div class="h-1 w-24 bg-red-600 mx-auto rounded"></div>
        </div>

        <!-- エラーメッセージ -->
        <div class="mb-8">
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">
                サーバーエラー
            </h2>
            <p class="text-gray-600 text-lg leading-relaxed">
                申し訳ございません。サーバーで問題が発生しました。<br>
                しばらく時間をおいてから再度お試しください。
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
                
                <button onclick="location.reload()" 
                        class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition-colors duration-200 shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    ページを再読み込み
                </button>
            </div>
        </div>

        <!-- エラー詳細（開発環境のみ） -->
        @if(config('app.debug') && isset($exception))
        <div class="mt-12 p-6 bg-red-50 rounded-lg border border-red-200 text-left">
            <h3 class="text-lg font-semibold text-red-800 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                エラー詳細（開発環境）
            </h3>
            <div class="bg-white p-4 rounded border border-red-300 overflow-auto">
                <p class="text-sm text-gray-800 font-mono">
                    <strong>Message:</strong> {{ $exception->getMessage() }}<br>
                    <strong>File:</strong> {{ $exception->getFile() }}<br>
                    <strong>Line:</strong> {{ $exception->getLine() }}
                </p>
            </div>
        </div>
        @else
        <!-- 補足情報（本番環境） -->
        <div class="mt-12 p-6 bg-yellow-50 rounded-lg border border-yellow-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                問題が解決しない場合
            </h3>
            <div class="text-gray-700 text-sm space-y-2">
                <p>以下をお試しください：</p>
                <ul class="list-disc list-inside space-y-1 text-left max-w-md mx-auto">
                    <li>ブラウザのキャッシュをクリアする</li>
                    <li>しばらく時間をおいてから再度アクセスする</li>
                    <li>別のブラウザで試してみる</li>
                </ul>
                <p class="mt-4 pt-4 border-t border-yellow-300">
                    問題が継続する場合は、システム管理者にお問い合わせください。
                </p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection




