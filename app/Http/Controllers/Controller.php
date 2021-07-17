<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected object $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function responseJson(array $data): JsonResponse
    {
        return response()->json($data);
    }

    /**
     * 接口返回格式
     * @param int $code
     * @param string $msg
     * @param array $data
     * @param int|null $count 兼容layuimini条数
     * @return JsonResponse
     */
    protected function response(int $code, string $msg, array $data = [], ?int $count = null): JsonResponse
    {
        $response = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];
        if ($count !== null) {
            $response['count'] = $count;
        }
        return $this->responseJson($response);
    }

}
