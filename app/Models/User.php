<?php

namespace App\Models;

use App\Models\Traits\SerializeDate;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Libraries\Role as RoleLib;

/**
 * @method static find(int $id)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use SoftDeletes, HasRoles, SerializeDate;

    const USABLE   = 1; //可用
    const DISABLED = 0; //不可用

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * 是否是超级管理员
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleLib::SUPER_ADMIN);
    }


    /**
     * 获取当前用户的角色id，目前系统采用的是单一角色模式
     * @return int|null
     */
    public function getRoleId(): ?int
    {
        $roleIds = $this->roles()->pluck('id')->toArray();
        return $roleIds[0] ?: null;
    }
}
