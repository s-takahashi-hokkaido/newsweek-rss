<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // 詳細なエラーログを記録
        $this->reportable(function (Throwable $e) {
            // 404エラーは通常のアクセスログとして扱う（詳細ログは不要）
            if ($e instanceof NotFoundHttpException) {
                Log::info('404 Not Found', [
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return;
            }

            // その他のHTTPエラー（500など）は詳細にログ記録
            if ($e instanceof HttpException) {
                Log::error('HTTP Exception', [
                    'status_code' => $e->getStatusCode(),
                    'message' => $e->getMessage(),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                return;
            }

            // 一般的な例外は最も詳細にログ記録
            Log::error('Application Exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        });
    }

    /**
     * レスポンスのカスタマイズ
     *
     * エラーページに追加情報を渡す
     */
    public function render($request, Throwable $e)
    {
        // カスタムエラーページにexceptionを渡す
        if ($this->shouldReturnJson($request, $e)) {
            return parent::render($request, $e);
        }

        // 500エラーの場合、例外情報をビューに渡す
        if ($e instanceof \Exception && !$e instanceof HttpException) {
            if (view()->exists('errors.500')) {
                return response()->view('errors.500', [
                    'exception' => $e,
                ], 500);
            }
        }

        return parent::render($request, $e);
    }
}
