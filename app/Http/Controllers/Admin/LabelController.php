<?php

namespace App\Http\Controllers\Admin;

use App\Libraries\Code;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class LabelController extends BaseController
{
    /**
     * 获取当前角色标签
     * @return JsonResponse
     */
    public function getRoles(): JsonResponse
    {
        // 如果是管理员就搜索全部，其他搜索下级角色
        $builder = Role::query();
        // 不是超级管理员查询下级
        if (!$this->user()->isSuperAdmin()) {
            $roleId = $this->user()->getRoleId();
            if ($roleId === null) {
                $this->response(Code::SUCCESS, '', []);
            }
            $builder->where('pid', '=', $roleId);
        }

        $list = $builder->pluck('name', 'id')->toArray();
        return $this->response(Code::SUCCESS, '', $list);
    }
}
