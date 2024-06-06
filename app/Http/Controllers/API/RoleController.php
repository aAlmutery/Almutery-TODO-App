<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class RoleController extends Controller
{
    public static function isOwner($user_id){
        return User::role('Owner')->where('id', $user_id)->count() > 0 ? true : false ;
    }

    public static function isDeveloper($user_id){
        return User::role('Developer')->where('id', $user_id)->count() > 0 ? true : false ;
    }

    public static function isTester($user_id){
        return User::role('Tester')->where('id', $user_id)->count() > 0 ? true : false ;
    }

}
