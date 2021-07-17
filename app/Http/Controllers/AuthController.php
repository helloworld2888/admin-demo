<?php

namespace App\Http\Controllers;

use App\Libraries\Code;
use App\Libraries\PasswordHelper;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 后台登陆
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(): JsonResponse
    {
        $rules    = [
            'name'     => 'required|string|between:5,18',
            'password' => 'required|string|between:6,30',
            'captcha'  => 'required|captcha',
            'keep'     => 'required|integer|in:0,1',
        ];
        $messages = [
            'name.*'     => '账号异常,应该为5-18位',
            'password.*' => '密码异常,应该为6-30位',
            'captcha.*'  => '验证码错误',
            'keep.*'     => '连接参数异常',
        ];
        $params   = $this->validate($this->request, $rules, $messages);
        $name     = $params['name'];
        $password = $params['password'];
        $keep     = $params['keep'];

        $user = User::query()->where('name', '=', $name)->first();
        if (!$user) {
            return $this->response(Code::ERROR, '用户不存在');
        }
        $check = PasswordHelper::checkUserPassword($password, $user->password);
        if (!$check) {
            return $this->response(Code::ERROR, '密码错误');
        }
        Auth::guard('web')->login($user, true);

        return $this->response(Code::SUCCESS, '登录成功');
    }

    /**
     * get CAPTCHA
     *
     * @return string
     */
    public function getCaptcha()
    {
        return captcha_img();
        return captcha_src();
    }
}
