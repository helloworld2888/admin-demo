<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Admin\BaseController;
use App\Libraries\Code;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PermissionController extends BaseController
{
    /**
     * 权限列表
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $builder = Permission::query();
        $count   = $builder->count();
        $list    = $builder
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
        return $this->response(Code::SUCCESS, '', $list, $count);
    }

    /**
     * 权限添加
     * @return JsonResponse
     * @throws ValidationException
     */
    public function add(): JsonResponse
    {
        $rules    = [
            'title' => 'required|string|between:2,16',
            'name'  => 'required|string',
            'pid'   => 'required|integer|min:0',
            'type'  => 'required|integer|in:0,1,2,3',
            'icon'  => 'nullable|string|max:128',
            'href'  => 'nullable|string|max:128',
            'sort'  => 'required|integer|min:0',
        ];
        $messages = [
            'title.*' => '权限名称应该为2-10位',
            'name.*'  => '权限标识格式错误',
            'pid.*'   => '权限上级id格式错误',
            'type.*'  => '类型格式异常',
            'icon.*'  => '图标长度应该不超过128位',
            'href.*'  => '链接长度应该不超过128位',
            'sort.*'  => '排序应该为大于等于0数字',
        ];
        $params   = $this->validate($this->request, $rules, $messages);

        // 权限保存
        $permission             = new Permission();
        $permission->title      = $params['title'];
        $permission->name       = $params['name'];
        $permission->pid        = $params['pid'];
        $permission->type       = $params['type'];
        $permission->icon       = $params['icon'] ?? '';
        $permission->href       = $params['href'] ?? '';
        $permission->sort       = $params['sort'];
        $permission->guard_name = 'admin';
        $result                 = $permission->save();
        if ($result) {
            return $this->response(Code::SUCCESS, '创建成功');
        } else {
            return $this->response(Code::ERROR, '创建失败');
        }
    }

    /**
     * 权限修改
     * @return JsonResponse
     * @throws ValidationException
     */
    public function edit(): JsonResponse
    {
        $rules    = [
            'id'    => 'required|integer',
            'title' => 'required|string|between:2,16',
            'name'  => 'required|string',
            'pid'   => 'required|integer|min:0',
            'type'  => 'required|integer|in:0,1,2,3',
            'icon'  => 'nullable|string|max:128',
            'href'  => 'nullable|string|max:128',
            'sort'  => 'required|integer|min:0',
        ];
        $messages = [
            'id.*'    => 'id异常',
            'title.*' => '权限名称应该为2-10位',
            'name.*'  => '权限标识格式错误',
            'pid.*'   => '权限上级id格式错误',
            'type.*'  => '类型格式异常',
            'icon.*'  => '图标长度应该不超过128位',
            'href.*'  => '链接长度应该不超过128位',
            'sort.*'  => '排序应该为大于等于0数字',
        ];
        $params   = $this->validate($this->request, $rules, $messages);

        $permission = Permission::find($params['id']);
        if (!$permission) {
            return $this->response(Code::ERROR_USER, '权限不存在');
        }
        // 权限保存
        $permission->title = $params['title'];
        $permission->name  = $params['name'];
        $permission->pid   = $params['pid'];
        $permission->type  = $params['type'];
        $permission->icon  = $params['icon'] ?? '';
        $permission->href  = $params['href'] ?? '';
        $permission->sort  = $params['sort'];
        $result            = $permission->save();
        if ($result) {
            return $this->response(Code::SUCCESS, '保存成功');
        } else {
            return $this->response(Code::ERROR, '保存失败');
        }
    }

    /**
     * 删除权限
     * @return JsonResponse
     * @throws ValidationException
     */
    public function delete(): JsonResponse
    {
        $rules    = [
            'id' => 'required|integer',
        ];
        $messages = [
            'id.*' => 'id异常',
        ];
        $params   = $this->validate($this->request, $rules, $messages);
        $id       = $params['id'];

        $permission = Permission::find($id);
        if (!$permission) {
            return $this->response(Code::ERROR_USER, '权限不存在');
        }

        $result = $permission->delete();
        if ($result) {
            return $this->response(Code::SUCCESS, '刪除成功');
        } else {
            return $this->response(Code::ERROR, '刪除失败');
        }
    }
}
