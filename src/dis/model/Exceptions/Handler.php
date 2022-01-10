<?php

namespace App\Exceptions;

use App\utils\ResponseHelper;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *a
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            return ResponseHelper::errorMsg('参数错误', 400, Arr::first(Arr::collapse($exception->errors())));
        }
        if ($request->is('api/*')) {
            if ($this->isHttpException($exception)) {
                if ($exception->getStatusCode() == '404') return ResponseHelper::errorMsg('Link does not exist', 404);
            }
            return ResponseHelper::errorMsg('系统错误', $this->replaceCode((int)$exception->getCode(), 0, 500), $exception->getMessage());
        }
        return parent::render($request, $exception);
    }

    protected function replaceCode($code, $fit, $replace)
    {
        return $code === $fit ? $replace : $code;
    }
}
