<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use App\Imports\UserImport;
use Illuminate\Http\Request;
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

    /**
     * [changePassword description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'password'  => 'required'
        ]);
        $user = request()->user('api'); 
        $user->password = bcrypt($request->password);
        $user->save();

        return SendResponse::accept();
    }

    /**
     * [index description]
     * @return [type] [description]
     */
    public function index()
    {
        $perPage = request()->perPage ?: 10;

        $users = User::where('role','!=','admin');
        if (request()->q != '') {
            $users = $users->where('name', 'LIKE', '%'. request()->q.'%');
        }
        $users = $users->paginate($perPage);
        return SendResponse::acceptData($users);
    }

    /**
     * [store description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function store(Request $request) 
    {
        $request->validate([
            'name'      => 'required',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required'
        ]);

        try {
            $data = [
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => bcrypt($request->password),
                'role'      => 'guru'
            ];

            User::create($data);
        } catch (\Exception $e) {
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * [show description]
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function show(User $user)
    {
        return SendResponse::acceptData($user);
    }

    /**
     * [update description]
     * @param  Request $request [description]
     * @param  User    $user    [description]
     * @return [type]           [description]
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'required',
            'email'     => 'required|email|unique:users,email,'.$user->id
        ]);

        try {
            $data = [
                'name'  => $request->name,
                'email' => $request->email,
            ];
            if($request->password != '') {
                $data['password'] = bcrypt($request->password);
            }

            $user->update($data);
        } catch (\Exception $e) {
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * [destroy description]
     * @param  User   $user [description]
     * @return [type]       [description]
     */
    public function destroy(User $user)
    {
        $user->delete();
        return SendResponse::accept();
    }

    /**
     * [import description]
     * @param  Request $request [description]
     * @return [type]           [description]
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
     * Delete user multiple
     *
     * @author shellrean <wandinak17@gmail.com>
     * @param \Illuminate\Http\Request $request
     * @return \App\Actions\SendResponse
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