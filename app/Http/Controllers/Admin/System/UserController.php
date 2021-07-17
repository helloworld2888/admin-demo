<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Admin\BaseController;
use App\Libraries\Code;
use App\Libraries\PasswordHelper;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserController extends BaseController
{
    /**
     * 用户列表
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
            'name'       => 'nullable|string',
            'role_id'    => 'nullable|integer|min:1',
        ];
        $messages  = [
            'page.*'       => '分页页数异常',
            'limit.*'      => '分页条数最大1000',
            'sort_field.*' => '排序字段异常',
            'sort_type.*'  => '排序方式异常',
            'name.*'       => '账号异常',
            'role_id.*'    => '角色异常',
        ];
        $params    = $this->validate($this->request, $rules, $messages);
        $page      = $params['page'] ?? 1; // 页码
        $limit     = $params['limit'] ?? 10; // 条数
        $sortField = $params['sort_field'] ?? '' ?: 'id'; // 排序字段
        $sortType  = $params['sort_type'] ?? '' ?: 'desc'; // 排序排序类型
        $name      = $params['name'] ?? ''; // 账号
        $roleId    = $params['role_id'] ?? ''; // 角色id
        $offset    = ($page - 1) * $limit;

        $builder = User::query()
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id');
            })
            ->selectRaw('users.*,model_has_roles.role_id');
        if ($name) {
            $builder->where('name', 'like', "%{$name}%");
        }
        // 不是超级管理员查询下级
        if (!$this->user()->isSuperAdmin()) {
            $builder->where('pid', '=', $this->user()->id);
        }
        if ($roleId) {
            $builder->where('role_id', '=', $roleId);
        }

        $count = $builder->count();
        $list  = $builder
            ->orderBy($sortField, $sortType)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
        if ($list) {
            $userMap = User::all()->pluck('name', 'id')->toArray();
            foreach ($list as $key => $value) {
                $list[$key]['pid_name'] = isset($userMap[$value['pid']]) ? $userMap[$value['pid']]."({$value['pid']})" : '-';;
            }
        }

        return $this->response(Code::SUCCESS, '', $list, $count);
    }

    /**
     * 用户添加
     * @return JsonResponse
     * @throws ValidationException
     */
    public function add(): JsonResponse
    {
        $rules    = [
            'name'     => 'required|alpha_num|between:5,18',
            'password' => 'required|string|between:6,30',
            'email'    => 'required|email:rfc,dns',
            'role_id'  => 'required|integer|min:1',
        ];
        $messages = [
            'name.*'     => '账号应该为5-18位，数字字符串组合',
            'password.*' => '密码应该为6-30位',
            'email.*'    => '邮箱格式异常',
            'role_id.*'  => '角色不能为空',
        ];
        $params   = $this->validate($this->request, $rules, $messages);
        $name     = $params['name'];
        $email    = $params['email'];
        $roleId   = $params['role_id'];

        // 唯一校验
        $checkName = User::query()->where('name', $name)->exists();
        if ($checkName) {
            return $this->response(Code::ERROR_VALIDATION, '用户名已经存在,请更换');
        }
        $checkEmail = User::query()->where('email', $email)->exists();
        if ($checkEmail) {
            return $this->response(Code::ERROR_VALIDATION, '邮箱已经存在,请更换');
        }

        // 角色校验
        $role = Role::findById($roleId);
        if (!$role) {
            return $this->response(Code::ERROR_VALIDATION, '角色异常');
        }
        // 检查选中的角色是否有选择权限
        if (!$this->_checkSetRole($this->user(), $role)) {
            return $this->response(Code::ERROR_VALIDATION, '选取角色异常');
        }

        // 用户保存
        $user           = new User();
        $user->name     = $name;
        $user->email    = $email;
        $user->pid      = $this->user()->id;
        $user->password = PasswordHelper::createUserPassword($params['password']); // 创建密码
        $result         = $user->save();
        if ($result) {
            $user->assignRole($role); // 分配角色
            return $this->response(Code::SUCCESS, '创建成功');
        } else {
            return $this->response(Code::ERROR, '创建失败');
        }
    }

    /**
     * 用户修改
     * @return JsonResponse
     * @throws ValidationException
     */
    public function edit(): JsonResponse
    {
        $rules    = [
            'id'    => 'required|integer',
            'email' => 'nullable|string|email:rfc,dns',
        ];
        $messages = [
            'id.*'    => 'id异常',
            'email.*' => '邮箱格式异常',
        ];
        $params   = $this->validate($this->request, $rules, $messages);
        $id       = $params['id'];
        $email    = $params['email'];

        $user = User::find($id);
        if (!$user) {
            return $this->response(Code::ERROR_USER, '用户不存在');
        }
        // 检测当前用户是否有操作权限
        if (!$this->_checkUserOpt($this->user(), $user)) {
            return $this->response(Code::ERROR_USER, '当前用户无权限操作');
        }

        // 检测邮箱
        $checkEmail = User::query()->where('email', $email)->where('id', '!=', $id)->exists();
        if ($checkEmail) {
            return $this->response(Code::ERROR_VALIDATION, '邮箱已经存在,请更换');
        }

        $user->email = $email;
        $result      = $user->save();
        if ($result) {
            return $this->response(Code::SUCCESS, '保存成功');
        } else {
            return $this->response(Code::ERROR, '保存失败');
        }
    }

    /**
     * 删除用户
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

        $user = User::find($id);
        if (!$user) {
            return $this->response(Code::ERROR_USER, '用户不存在');
        }
        // 检测当前用户是否有操作权限
        if (!$this->_checkUserOpt($this->user(), $user)) {
            return $this->response(Code::ERROR_USER, '当前用户无权限操作');
        }

        $result = $user->delete();
        if ($result) {
            return $this->response(Code::SUCCESS, '刪除成功');
        } else {
            return $this->response(Code::ERROR, '刪除失败');
        }
    }

    /**
     * 设置状态
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setStatus(): JsonResponse
    {
        $rules    = [
            'id'     => 'required|integer',
            'status' => 'required|integer|in:0,1',
        ];
        $messages = [
            'id.*'     => 'id异常',
            'status.*' => '状态参数异常',
        ];
        $params   = $this->validate($this->request, $rules, $messages);
        $id       = $params['id'];
        $status   = $params['status'];

        $user = User::find($id);
        if (!$user) {
            return $this->response(Code::ERROR_USER, '用户不存在');
        }
        // 检测当前用户是否有操作权限
        if (!$this->_checkUserOpt($this->user(), $user)) {
            return $this->response(Code::ERROR_USER, '当前用户无权限操作');
        }

        $user->status = $status;
        $result       = $user->save();
        if ($result) {
            return $this->response(Code::SUCCESS, '修改成功');
        } else {
            return $this->response(Code::ERROR, '修改失败');
        }
    }

    /**
     * 设置用户角色
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setRole(): JsonResponse
    {
        $rules    = [
            'id'      => 'required|integer',
            'role_id' => 'required|integer|min:1',
        ];
        $messages = [
            'id.*'      => 'id异常',
            'role_id.*' => '角色不能为空',
        ];
        $params   = $this->validate($this->request, $rules, $messages);
        $id       = $params['id'];
        $roleId   = $params['role_id'];

        $user = User::find($id);
        if (!$user) {
            return $this->response(Code::ERROR_USER, '用户不存在');
        }
        // 检测当前用户是否有操作权限
        if (!$this->_checkUserOpt($this->user(), $user)) {
            return $this->response(Code::ERROR_USER, '当前用户无权限操作');
        }

        // 角色校验
        $role = Role::findById($roleId);
        if (!$role) {
            return $this->response(Code::ERROR_VALIDATION, '角色异常');
        }
        // 检查选中的角色是否有选择权限
        if (!$this->_checkSetRole($this->user(), $role)) {
            return $this->response(Code::ERROR_VALIDATION, '选取角色异常');
        }

        $user->syncRoles($role);
        return $this->response(Code::SUCCESS, '保存成功');
    }

    /**
     * 设置用户密码
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setPassword(): JsonResponse
    {
        $rules    = [
            'id'       => 'required|integer',
            'password' => 'required|string|between:6,30',
        ];
        $messages = [
            'id.*'       => 'id异常',
            'password.*' => '密码应该为6到30位',
        ];
        $params   = $this->validate($this->request, $rules, $messages);
        $id       = $params['id'];
        $password = $params['password'];

        $user = User::find($id);
        if (!$user) {
            return $this->response(Code::ERROR_USER, '用户不存在');
        }
        // 检测当前用户是否有操作权限
        if (!$this->_checkUserOpt($this->user(), $user)) {
            return $this->response(Code::ERROR_USER, '当前用户无权限操作');
        }

        $user->password = PasswordHelper::createUserPassword($password);
        $result         = $user->save();
        if ($result) {
            return $this->response(Code::SUCCESS, '保存成功');
        } else {
            return $this->response(Code::ERROR, '保存失败');
        }
    }

    /**
     * 检查设置角色权限 非超级管理员组,只能添加当前角色的下级角色 todo 后面优化成全部下级
     * @param User $optUser
     * @param Role $role
     * @return bool
     */
    private function _checkSetRole(User $optUser, Role $role): bool
    {
        if ($optUser->isSuperAdmin()) {
            return true;
        }

        $pid     = $optUser->getRoleId();
        $roleIds = Role::query()
            ->where('pid', '=', $pid)
            ->pluck('id')
            ->toArray();
        if (in_array($role->id, $roleIds)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查用户操作 非超级管理员组,只能操作当前用户的下级用户 todo 后面优化成全部下级
     * @param User $optUser
     * @param User $user
     * @return bool
     */
    private function _checkUserOpt(User $optUser, User $user): bool
    {
        if ($optUser->isSuperAdmin()) {
            return true;
        }

        if ($user->pid == $optUser->id) {
            return true;
        } else {
            return false;
        }
    }
}
