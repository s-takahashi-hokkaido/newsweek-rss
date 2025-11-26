@extends('layouts.app')

@section('title', 'ニューズウィーク日本版 記事検索')

@section('content')
    {{-- RSS取得状況表示 --}}
    <x-rss-status 
        :latest-fetch="$latest_fetch" 
        :latest-success="$latest_success" 
        :is-fetch-healthy="$is_fetch_healthy" 
    />

    {{-- 検索フォーム --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">🔍 記事を検索</h2>
        
        <form method="GET" action="{{ route('articles.index') }}" class="space-y-4">
            {{-- 日付範囲 --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">
                        📅 公開日（以降）
                    </label>
                    <input 
                        type="date" 
                        name="date_from" 
                        id="date_from" 
                        value="{{ old('date_from', $conditions['date_from'] ?? '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    >
                    @error('date_from')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">
                        📅 公開日（以前）
                    </label>
                    <input 
                        type="date" 
                        name="date_to" 
                        id="date_to" 
                        value="{{ old('date_to', $conditions['date_to'] ?? '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    >
                    @error('date_to')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- URL --}}
            <div>
                <label for="url" class="block text-sm font-medium text-gray-700 mb-1">
                    🔗 URL（完全一致）
                </label>
                <input 
                    type="text" 
                    name="url" 
                    id="url" 
                    value="{{ old('url', $conditions['url'] ?? '') }}"
                    placeholder="例: https://www.newsweekjapan.jp/stories/..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                >
                @error('url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- タイトル --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    📝 タイトル（部分一致）
                </label>
                <input 
                    type="text" 
                    name="title" 
                    id="title" 
                    value="{{ old('title', $conditions['title'] ?? '') }}"
                    placeholder="例: ウクライナ、AI、経済"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                >
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- ボタン --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <button 
                    type="submit" 
                    class="flex-1 sm:flex-none px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-colors"
                >
                    🔍 検索する
                </button>
                
                <a 
                    href="{{ route('articles.index') }}" 
                    class="flex-1 sm:flex-none px-6 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 focus:ring-4 focus:ring-gray-300 transition-colors text-center"
                >
                    🔄 クリア
                </a>
            </div>
        </form>
    </div>

    {{-- 検索結果 --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">
                📰 検索結果
            </h2>
            <span class="text-sm text-gray-600">
                <span class="font-bold text-blue-600">{{ number_format($articles->total()) }}</span> 件
            </span>
        </div>

        @if($articles->count() > 0)
            {{-- 記事一覧テーブル --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                    公開日
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    タイトル
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    記事内容（抜粋）
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    URL
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($articles as $article)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <time datetime="{{ $article->published_at->format('Y-m-d') }}">
                                            {{ $article->published_at->format('Y年m月d日') }}
                                        </time>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <a href="{{ $article->url }}" target="_blank" rel="noopener noreferrer" class="hover:text-blue-600 transition-colors duration-200">
                                            {{ $article->title }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 max-w-md">
                                        <div class="line-clamp-3">
                                            {{ Str::limit(strip_tags($article->content), 200) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ $article->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center">
                                            <span class="truncate max-w-xs">{{ Str::limit($article->url, 40) }}</span>
                                            <svg class="w-4 h-4 ml-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ページネーション --}}
            <div class="mt-6">
                {{ $articles->appends($conditions)->links() }}
            </div>
        @else
            {{-- 結果なし --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-600 text-lg mb-2">検索条件に一致する記事が見つかりませんでした</p>
                <p class="text-gray-500 text-sm">検索条件を変更して再度お試しください</p>
            </div>
        @endif
    </div>
@endsection

