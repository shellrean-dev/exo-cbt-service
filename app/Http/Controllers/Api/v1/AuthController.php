<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Auth;

/**
 * AuthController
 * @author shellrean <wandinak17@gmail.com>
 */
class AuthController extends Controller
{
    /**
     * @Route(path="api/v1/login", methods={"POST"})
     * 
     * Get api token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = request(['email', 'password']);
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token =  $user->createToken('personal-token')->plainTextToken;
            return SendResponse::acceptCustom([
                'status' => 'success',
                'token' => $token
            ]);
        }
        return SendResponse::acceptData('invalid-credentials');
    }
}
