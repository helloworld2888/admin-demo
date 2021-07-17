<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class IndexController extends BaseController
{
    /**
     * layuimini 获取初始化数据
     * @return JsonResponse
     */
    public function getInit(): JsonResponse
    {
        $initData = [
            'homeInfo' => [
                'title' => '首页',
                'href'  => route('admin.view.welcome'),
            ],
            'logoInfo' => [
                'title' => '',
                'image' => URL::asset('/').'resources/layuimini-2-onepage/images/logo.png',
            ],
            'menuInfo' => $this->getMenuList()
        ];
        return response()->json($initData);
    }

    /**
     * 获取菜单列表
     * @return array
     */
    private function getMenuList(): array
    {
        $user        = $this->user();
        $permissions = null;
        if (!$user->isSuperAdmin()) {
            $permissions = $user->getPermissionsViaRoles()->pluck('id')->toArray();
        }
        $menuList = DB::table('permissions')
            ->select(['id', 'pid', 'title', 'icon', 'href', 'target'])
            ->where('type', '=', 0)
            ->orderBy('sort', 'asc')
            ->get();
        $menuList = $this->_buildMenuChild(0, $menuList, $permissions);
        return $menuList;
    }

    /**
     * 递归获取子菜单
     * @param int $pid
     * @param $menuList
     * @param array|null $permissions
     * @return array
     */
    private function _buildMenuChild(int $pid, $menuList, ?array $permissions = null): array
    {
        $treeList = [];
        foreach ($menuList as $v) {
            if ($pid == $v->pid) {
                $node  = (array)$v;
                $child = $this->_buildMenuChild($v->id, $menuList, $permissions);
                if (!empty($child)) {
                    $node['child'] = $child;
                }
                if ($permissions == null) { // 超级管理员是null
                    $treeList[] = $node;
                } else {
                    if (in_array($v->id, $permissions)) {
                        $treeList[] = $node;
                    }
                }
            }
        }
        return $treeList;
    }
}
