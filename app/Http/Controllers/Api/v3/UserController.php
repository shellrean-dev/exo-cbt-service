<?php

namespace App\Http\Controllers\Api\v3;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use ShellreanDev\Services\User\UserService;
/**
 * UserController Controller
 * @author shellrean <wandinak17@gmail.com>
 */
class UserController extends Controller
{
    /**
     * @Route(path="api/v3/users", method={"GET"})
     */
    public function index(Request $request, UserService $userService)
    {
        $limit = intval($request->limit);
        $limit = $limit ? $limit : 10;

        $condition = is_array($request->conditions);
        $condition = $condition ? $request->conditions : [];

        // show only teacher
        $condition[] = ['role','=','guru'];
        
        $users = $userService->paginate($condition, $limit);
        if (!$users) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($users);
    }

    /**
     * @Route(path="api/v3/users", methods={"POST"})
     */
    public function store(Request $request, UserService $userService)
    {
        $request->validate([
            'name'      => 'required',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required'
        ]);

        $allowed = [
            'name' => '',
            'email' => '',
            'password' => '',
            'role' => ''
        ];
        $request->merge([
            'role' => 'guru',
            'password' => bcrypt($request->password),
        ]);

        $stored = $userService->store((object) array_intersect_key($request->all(), $allowed));
        if (!$stored) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($stored);
    }

    /**
     * @Route(path="api/v3/users/{id}", methods={"GET"})
     */
    public function show(string $id, UserService $userService)
    {
        $find = $userService->findOne($id);
        if (!$find) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($find);
    }

    /**
     * @Route(path="api/v3/users/{id}", methods={"PUT"})
     */
    public function update(string $id, Request $request, UserService $userService)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id
        ]);

        $allowed = [
            'id' => '',
            'name' => '',
            'email' => '',
            'password' => ''
        ];

        $request->merge(['id' => $id]);
        if ($request->password != '') {
            $request->merge(['password' => bcrypt($request->password)]);
        }

        $update = $userService->update((object) array_intersect_key($request->all(), $allowed));
        if (!$update) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($update);
    }
}