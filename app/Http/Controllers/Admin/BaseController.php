<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    /**
     * @var User
     */
    private $user = null;

    /**
     * @return User
     */
    public function user(): User
    {
        if ($this->user == null) {
            $this->user = Auth::guard('web')->user();
        }
        return $this->user;
    }
}
