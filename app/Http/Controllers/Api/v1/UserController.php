<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\SendResponse;
use App\User;

class UserController extends Controller
{
    /**
     * Get current user login
     *
     * @return /Illuminate/Http/Response
     **/
    public function getUserLogin()
    {
        $user = request()->user('api'); 
        return SendResponse::acceptData($user);
    }

    /**
     *  Get all users 
     *  
     * @return \App\Actions\SendResponse
     */
    public function userLists()
    {
        $users = User::orderBy('created_at')->get();
        return SendResponse::acceptData($users);
    }
}
