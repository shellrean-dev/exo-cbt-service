<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

use App\Actions\SendResponse;
use App\Imports\UserImport;
use App\User;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Browser;

/**
 * UserController
 * @author shellrean <wandinak17@gmail.com>
 */
class UserController extends Controller
{
    /**
     * @Route(path="api/v1/user-authenticated", methods={"GET"})
     *
     * Get current user login
     *
     * @return App\Actions\SendResponse
     **/
    public function getUserLogin()
    {
        $user = request()->user();

        $menus = DB::table('role_menuses')
            ->where('role', $user->role)
            ->get()->mapWithKeys(function($rm, $key) {
                return [$rm->code => true];
            });

        $user->ip = request()->ip();
        $user->browser = Browser::browserName();
        $user->flatform = Browser::platformName();
        $user->menus =$menus;
        return SendResponse::acceptData($user);
    }

    /**
     * @Route(path="api/v1/user-lists", methods={"GET"})
     *
     *  Get all users
     *
     * @return App\Actions\SendResponse
     */
    public function userLists()
    {
        $users = User::orderBy('created_at')->get();
        return SendResponse::acceptData($users);
    }

    /**
     * @Route(path="api/v1/user/change-password", methods={"POST"})
     *
     * @param  Illuminate\Http\Request $request
     * @return App\Actions\SendResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'password'  => 'required'
        ]);
        $user = request()->user();
        $user->password = bcrypt($request->password);
        $user->save();

        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/users", methods={"GET"})
     *
     * @return App\Actions\SendResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $perPage = request()->perPage ?: 10;

        $users = DB::table('users')->where('id', '<>', $user->id);
        if (request()->q != '') {
            $users = $users->where('name', 'LIKE', '%'. request()->q.'%');
        }
        $users = $users->paginate($perPage);
        return SendResponse::acceptData($users);
    }

    /**
     * @Route(path="api/v1/users", methods={"POST"})
     *
     * @param  Illuminate\Http\Request $request
     * @return App\Actions\SendResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required',
            'email'     => 'required|email|unique:users,email',
            'role'      => 'required',
            'password'  => 'required'
        ]);

        try {
            $data = [
                'id'        => Str::uuid(),
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => bcrypt($request->password),
                'role'      => $request->role,
                'created_at'=> now(),
                'updated_at'=> now()
            ];

            DB::table('users')->insert($data);
        } catch (\Exception $e) {
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/users/{user}", methods={"GET"})
     *
     * @param  App\User   $user
     * @return App\Actions\SendResponse
     */
    public function show(User $user)
    {
        return SendResponse::acceptData($user);
    }

    /**
     * @Route(path="api/v1/users/{user}, methods={"PUT", "PATCH"})
     *
     * @param  Illuminate\Http\Request $request
     * @param  App\User    $user
     * @return App\Actions\SendResponse
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'required',
            'role'      => 'required',
            'email'     => 'required|email|unique:users,email,'.$user->id
        ]);

        try {
            $data = [
                'name'  => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'updated_at' => now()
            ];
            if($request->password != '') {
                $data['password'] = bcrypt($request->password);
            }

            DB::table('users')->where('id', $user->id)->update($data);
        } catch (\Exception $e) {
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/users/{user}", methods={"DELETE"})
     *
     * @param  App\User   $user
     * @return App\Actions\SendResponse
     */
    public function destroy(User $user)
    {
        $user->delete();
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/users/upload", methods={"POST"})
     *
     * @param  Illuminate\Http\Request $request]
     * @return App\Actions\SendResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        DB::beginTransaction();
        try {
            Excel::import(new UserImport, $request->file('file'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/users/delete-multiple", methods={"POST"})
     *
     * Delete user multiple
     *
     * @param Illuminate\Http\Request $request
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            User::whereIn('id', $request->user_id)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest('Error: '.$e->getMessage());
        }
        return SendResponse::accept();
    }
}
