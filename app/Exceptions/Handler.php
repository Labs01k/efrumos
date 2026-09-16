<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Пакет фото оттенков больше post_max_size: PHP выбросил весь POST,
        // и вместо страницы 413 возвращаем администратора к форме с понятной
        // ошибкой. Сессии здесь ещё нет (проверка идёт до неё), поэтому
        // ошибка едет параметром адреса.
        $this->renderable(function (PostTooLargeException $e, $request) {
            if (!preg_match('~/back/goods/shades/~', $request->path() . '/')) {
                return null;
            }

            $back = $request->headers->get('referer') ?: url($request->segment(1) . '/back/goods/shades/massupload');
            $back = preg_replace('~([?&])upload_error=[^&]*&?~', '$1', $back);

            return redirect(rtrim($back, '?&') . (str_contains($back, '?') ? '&' : '?') . 'upload_error=batch_size');
        });
    }
}
