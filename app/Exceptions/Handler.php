<?php

namespace App\Exceptions;

use App\Libraries\Code;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

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

        $this->renderable(function (Throwable $e, $request) {
            // 参数验证器
            if ($e instanceof ValidationException) {
                $data = [
                    'code' => Code::ERROR_VALIDATION,
                    'msg'  => '参数错误',
                ];
                foreach ($e->errors() as $p => $errors) {
                    $data['msg'] = array_shift($errors);
                }
                return response()->json($data);
            }
        });
    }
}
