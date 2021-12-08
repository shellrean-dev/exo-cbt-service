<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\Setting;
use App\User;
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
            // if ($user->deleted_at != null) {
            //     return SendResponse::acceptData('invalid-credentials');
            // }
            
            $token =  $user->createToken('Personal Access Token')->accessToken;
            return SendResponse::acceptCustom([
                'status' => 'success',
                'token' => $token
            ]);
        }
        return SendResponse::acceptData('invalid-credentials');
    }

    /**
     * @Route(path="api/v1/login/oauth", methods={"GET"})
     */
    public function oauth()
    {
        if(isset(request()->token) && ! empty(request()->token)) {
            $token = request()->token;
            $server_url = config('exo.oauth_server_url');
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
     * @Route(path="api/v1/login/sso", methods={"GET"})
     */
    public function sso()
    {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') 
            || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        
        $setting = Setting::where('name','airlock')->first();
        if($setting) {
            $query = http_build_query([
                'client_id' => $setting->value['client_id'],
                'redirect_uri' => $protocol.request()->server('HTTP_HOST').'/api/v1/login/callback',
                'response_type' => 'code',
                'scope' => ''
            ]);

            return redirect($setting->value['server_url'].'?'.$query);
        }
        return SendResponse::badRequest('Airlock has not configure');
    }

    /**
     * @Route(path="api/v1/login/callback", methods={"GET"})
     */
    public function callback()
    {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') 
            || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        
        $setting = Setting::where('name','airlock')->first();
        if($setting) {
            $server_url = $setting->value['token_url'];
            $response = Http::post($server_url, [
                'grant_type' => 'authorization_code',
                'client_id' => $setting->value['client_id'],
                'client_secret' => $setting->value['client_secret'],
                'redirect_uri' => $protocol.request()->server('HTTP_HOST').'/api/v1/login/callback',
                'code' => request()->code,
            ]);

            $res = $response->json();
            if(isset($res['access_token'])) {
                $token = $res['access_token'];

                return redirect($setting->value['consumer_url'].'/'.$token);
            }
            return SendResponse::badRequest('Single Sign On Failed. User mismatch or clash with existing data and SSO can not complete');
        }
        return SendResponse::badRequest('Airlock has not configure');
    }
}
