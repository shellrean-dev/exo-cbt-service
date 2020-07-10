<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\SendResponse;

class UserController extends Controller
{
    /**
     * Get all user list
     *
     * @return /Illuminate/Http/Response
     **/
    public function getUserLogin()
    {
        $user = request()->user('api'); 
        return SendResponse::acceptData($user);
    }
}
