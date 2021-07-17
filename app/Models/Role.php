<?php

namespace App\Models;

use App\Models\Traits\SerializeDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @method static find(int $id)
 */
class Role extends SpatieRole
{
    use HasFactory, SerializeDate;
}
