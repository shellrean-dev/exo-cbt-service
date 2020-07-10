<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Auth;

class AuthController extends Controller
{
    /**
     * Login to Api
     *
     * @author shellrean <wandinak17@gmail.com>
     * @param /ILluminate/Http/Request $request
     * @return /App/Actions/SendResponse
     **/
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
        ]);

        $credentials = request(['email', 'password']);
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token =  $user->createToken('Personal Access Token')->accessToken;
            return SendResponse::acceptCustom([
                'status' => 'success',
                'token' => $token
            ]);
        }
        return SendResponse::acceptData('invalid-credentials');
    }
}
