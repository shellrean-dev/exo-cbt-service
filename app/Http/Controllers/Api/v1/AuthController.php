<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\User;
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

    /**
     * [oauth description]
     * @return [type] [description]
     */
    public function oauth()
    {
        if(isset(request()->token) && ! empty(request()->token)) {
            $token = request()->token;
            $server_url = 'http://localhost:8000/api/user';
            $response = Http::withToken($token)->get($server_url);

            if($response->json() != '') {
                $res = $response->json();
                $user = User::where('email', $res['email'])->first();
                if($user) {
                    $token = $user->createToken('Personal Access Token')->accessToken;
                    return SendResponse::acceptCustom([
                        'status' => 'success',
                        'token' => $token
                    ]);
                }
            }

        }
        return SendResponse::badRequest('Single Sign On Failed. User mismatch or clash with existing data and SSO can not complete');
    }

    /**
     * [sso description]
     * @return [type] [description]
     */
    public function sso()
    {
        $query = http_build_query([
            'client_id' => 8,
            'redirect_uri' => 'http://127.0.0.1/api/v1/login/callback',
            'response_type' => 'code',
            'scope' => ''
        ]);

        return redirect('http://127.0.0.1:8000/oauth/authorize?'.$query);
    }

    /**
     * [callback description]
     * @return function [description]
     */
    public function callback()
    {
        $server_url = 'http://127.0.0.1:8000/oauth/token';
        $response = Http::post($server_url, [
            'grant_type' => 'authorization_code',
            'client_id' => 8,
            'client_secret' => 'zce9fznhdiP4amPZBpCTWrSoSwaZtn6Vg9x7oxJu',
            'redirect_uri' => 'http://127.0.0.1/api/v1/login/callback',
            'code' => request()->code,
        ]);

        $res = $response->json();
        if(isset($res['access_token'])) {
            return redirect('/auth/'.$res['access_token']);
        }
        return SendResponse::badRequest('Single Sign On Failed. User mismatch or clash with existing data and SSO can not complete');
    }
}
