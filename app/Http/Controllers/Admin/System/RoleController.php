<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Admin\BaseController;
use App\Libraries\Code;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RoleController extends BaseController
{
    /**
     * 角色列表
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(): JsonResponse
    {
        $rules     = [
            'page'       => 'required|integer|min:1',
            'limit'      => 'required|integer|min:1|max:1000',
            'sort_field' => 'nullable|string|in:id,created_at,updated_at',
            'sort_type'  => 'nullable|string|in:asc,desc',
            'title'      => 'nullable|string',
        ];
        $messages  = [
            'page.*'       => '分页页数异常',
            'limit.*'      => '分页条数最大1000',
            'sort_field.*' => '排序字段异常',
            'sort_type.*'  => '排序方式异常',
            'title'        => '角色名称异常',
        ];
        $params    = $this->validate($this->request, $rules, $messages);
        $page      = $params['page'] ?? 1; // 页码
        $limit     = $params['limit'] ?? 10; // 条数
        $sortField = $params['sort_field'] ?? '' ?: 'id'; // 排序字段
        $sortType  = $params['sort_type'] ?? '' ?: 'desc'; // 排序排序类型
        $title     = $params['title'] ?? ''; // 角色名称
        $offset    = ($page - 1) * $limit;

        $builder = Role::query();
        if ($title) {
            $builder->where('title', 'like', "%{$title}%");
        }

        $count = $builder->count();
        $list  = $builder
            ->orderBy($sortField, $sortType)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
        return $this->response(Code::SUCCESS, '', $list, $count);
    }

    /**
     * 角色添加
     * @return JsonResponse
     * @throws ValidationException
     */
    public function add(): JsonResponse
    {
        $rules    = [
            'name'  => 'required|alpha_num|between:4,18',
            'title' => 'required|string|between:2,10',
        ];
        $messages = [
            'name.*'  => '角色代号应该为4-18位，数字字符串组合',
            'title.*' => '角色名称应该为2-10位',
        ];
        $params   = $this->validate($this->request, $rules, $messages);
        $name     = $params['name'];
        $title    = $params['title'];

        // 唯一校验
        $checkName = Role::query()->where('name', $name)->exists();
        if ($checkName) {
            return $this->response(Code::ERROR_VALIDATION, '角色代号已经存在,请更换');
        }

        // 角色保存
        $role        = new Role();
        $role->name  = $name;
        $role->title = $title;
        $result      = $role->save();
        if ($result) {
            return $this->response(Code::SUCCESS, '创建成功');
        } else {
            return $this->response(Code::ERROR, '创建失败');
        }
    }

    /**
     * 角色修改
     * @return JsonResponse
     * @throws ValidationException
     */
    public function edit(): JsonResponse
    {
        $rules    = [
            'id'    => 'required|integer',
            'title' => 'required|string|between:2,10',
        ];
        $messages = [
            'id.*'    => 'id异常',
            'title.*' => '角色名称应该为2-10位',
        ];
        $params   = $this->validate($this->request, $rules, $messages);
        $id       = $params['id'];
        $title    = $params['title'];

        $role = Role::find($id);
        if (!$role) {
            return $this->response(Code::ERROR_USER, '角色不存在');
        }
        $role->title = $title;
        $result      = $role->save();
        if ($result) {
            return $this->response(Code::SUCCESS, '保存成功');
        } else {
            return $this->response(Code::ERROR, '失败');
        }
    }

    /**
     * 删除角色
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

        $role = Role::find($id);
        if (!$role) {
            return $this->response(Code::ERROR_USER, '角色不存在');
        }

        $result = $role->delete();
        if ($result) {
            return $this->response(Code::SUCCESS, '刪除成功');
        } else {
            return $this->response(Code::ERROR, '刪除失败');
        }
    }

    /**
     * 获取角色权限列表
     * @return JsonResponse
     * @throws ValidationException
     */
    public function getPermissions(): JsonResponse
    {
        $rules    = [
            'id' => 'required|integer|min:1',
        ];
        $messages = [
            'id.*' => '接口异常',
        ];
        $params   = $this->validate($this->request, $rules, $messages);
        $id       = $params['id'];

        // todo 只能分配下级角色权限
        $role = Role::find($id);
        if (!$role) {
            return $this->response(Code::ERROR_USER, '角色不存在');
        }
        $permissions = $role->permissions->pluck('id')->toArray();

        // todo 超级管理组查询全部，其他查询自己的权限
        $list = Permission::query()
            ->select(['id', 'pid', 'title'])
            ->where('guard_name', '=', 'admin')
//            ->whereIn()
            ->orderBy('pid', 'asc')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['checked'] = in_array($value['id'], $permissions);
        }

        $data = $this->_buildMenuChildren(0, $list);  // 获取被编辑角色原有权限
        return $this->response(Code::SUCCESS, '', $data);
    }

    /**
     * 设置角色权限列表
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setPermissions(): JsonResponse
    {
        $rules         = [
            'id'            => 'required|integer|min:1',
            'permissionIds' => 'nullable|array',
        ];
        $messages      = [
            'id.*'            => '接口异常',
            'permissionIds.*' => '权限数据异常',
        ];
        $params        = $this->validate($this->request, $rules, $messages);
        $id            = $params['id'];
        $permissionIds = $params['permissionIds'] ?? [];

        // todo 权限控制，只能保存下级权限
        $role = Role::find($id);
        if (!$role) {
            return $this->response(Code::ERROR_USER, '角色不存在');
        }
        // todo 判断传递权限必须是当前账号角色拥有的

        // 权限同步
        $role->syncPermissions($permissionIds);

        return $this->response(Code::SUCCESS, '', $params);
    }


    /**
     * 递归获取子菜单
     * @param int $pid
     * @param array $menuList
     * @return array
     */
    private function _buildMenuChildren(int $pid, array $menuList): array
    {
        $treeList = [];
        foreach ($menuList as $value) {
            if ($pid == $value['pid']) {
                $child = $this->_buildMenuChildren($value['id'], $menuList);
                if (!empty($child)) {
                    $value['checked']  = false; // 前端组件 - 文件夹勾选下级会全部勾选
                    $value['spread']   = true; // 前端组件 - 初始展开
                    $value['children'] = $child;
                }
                $treeList[] = $value;
            }
        }
        return $treeList;
    }
}
