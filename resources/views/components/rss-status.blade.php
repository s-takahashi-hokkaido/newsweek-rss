@props(['latestFetch', 'latestSuccess', 'isFetchHealthy'])

@if($latestFetch)
    @if($isFetchHealthy)
        {{-- 成功パターン --}}
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 font-medium">
                    最終更新: {{ $latestFetch->fetched_at->format('Y年m月d日 H:i') }}（正常に取得されました）
                </span>
            </div>
            @if($latestSuccess)
                <p class="text-green-700 text-sm mt-1 ml-7">
                    表示中のデータ: {{ $latestSuccess->fetched_at->format('Y年m月d日 H:i') }} 取得
                </p>
            @endif
        </div>
    @else
        {{-- 失敗パターン --}}
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800 font-medium">
                    最終取得: {{ $latestFetch->fetched_at->format('Y年m月d日 H:i') }}（取得に失敗しました）
                </span>
            </div>
            @if($latestFetch->error_message)
                <p class="text-red-700 text-sm mt-2 ml-7">
                    エラー: {{ $latestFetch->error_message }}
                </p>
            @endif
            @if($latestSuccess)
                <p class="text-red-700 text-sm mt-2 ml-7">
                    表示中のデータ: {{ $latestSuccess->fetched_at->format('Y年m月d日 H:i') }} 取得（前回成功時）
                </p>
            @else
                <p class="text-red-700 text-sm mt-2 ml-7">
                    表示可能なデータがありません
                </p>
            @endif
        </div>
    @endif
@else
    {{-- 取得履歴なしパターン --}}
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-gray-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <span class="text-gray-800 font-medium">
                まだRSSが取得されていません
            </span>
        </div>
        <p class="text-gray-700 text-sm mt-1 ml-7">
            <code class="bg-gray-100 px-2 py-1 rounded text-xs">php artisan rss:fetch</code> を実行してください
        </p>
    </div>
@endif

