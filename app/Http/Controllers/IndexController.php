<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function test()
    {

        $user = Auth::user();
        echo json_encode($user);
        die;
    }
}
