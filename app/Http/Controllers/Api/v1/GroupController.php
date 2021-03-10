<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Ambil data seluruh data grupping
     * 
     * @return \App\Actions\SendResponse
     * @author <wandinak17@gmail.com>
     */
    public function index()
    {
        try {
            $q = request()->q;
            $data = [];
            switch ($q) {
                case 'all':
                    $data = DB::table('groups')
                        ->get();
                break;
                case 'parent':
                    $data = DB::table('groups')
                        ->where('parent_id', 0)
                        ->get();
                break;
                default:
                    if(intval($q)) {
                        $data = DB::table('groups')
                            ->where('parent_id', intval($q))
                            ->get();
                    }
            }
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500. '.$e->getMessage());
        }
        return SendResponse::acceptData($data);
    }

    /**
     * Buat data baru grup
     * 
     * @param \Illuminate\Http\Request $request
     * @return \App\Actions\SendResponse
     * @author <wandinak17@gmail.com>
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required'
        ]);

        try {
            $data = [
                'name'      => $request->name,
                'created_at' => now(),
                'updated_at' => now()
            ];
            if (isset($request->parent_id) && $request->parent_id != '' && $request->parent_id != 0) {
                $exist = DB::table('groups')
                    ->where('id', $request->parent_id)
                    ->count();
                if ($exist < 1) {
                    return SendResponse::badRequest('data grup tidak ditemukan');
                }
                $data['parent_id'] = $request->parent_id;
            }

            DB::table('groups')->insert($data);
            return SendResponse::accept();
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage());
        }
    }

    /**
     * Ambil single data grup
     * 
     * @param int $group_id
     * @return \App\Actions\SendResponse
     * @author <wandinak17@gmail.com>
     */
    public function show($group_id)
    {
        try {
            $group = DB::table('groups')
                ->where('id', $group_id)
                ->select('id','parent_id','name')
                ->first();

            if (!$group) {
                return SendResponse::badRequest('kesalahan, data yang diminta tidak ditemukan');
            }
            return SendResponse::acceptData($group);
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage());
        }
    }

    /**
     * Edit data gorup
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $group_id
     * @return \App\Actions\SendResponse
     * @author <wandinak17@gmail.com>
     */
    public function update(Request $request, $group_id)
    {
        $group = DB::table('groups')
            ->where('id', $group_id)
            ->first();
        if (!$group) {
            return SendResponse::badRequest('data yang dikirimkan tidak ditemukan');
        }
        
        $request->validate([
            'name'  => 'required'
        ]);
        
        try {
            $data = [
                'name'  => $request->name,
            ];

            if (isset($request->parent_id) && $request->parent_id != '' && $request->parent_id != $group->parent_id) {
                $exist = DB::table('groups')
                    ->where('id', $request->parent_id)
                    ->count();
                if ($exist < 1) {
                    return SendResponse::badRequest('data grup tidak ditemukan');
                }
                $data['parent_id'] = $request->parent_id;
            }

            DB::table('groups')
                ->where('id', $group_id)
                ->update([
                    'name'  => $request->name,
                ]);
            return SendResponse::accept();
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage());
        }
    }

    /**
     * Remove data group
     * 
     * @param int $group_id
     * @return \App\Actions\SendResponse
     * @author <wandinak17@gmail.com>
     */
    public function destroy($group_id)
    {
        $group = DB::table('groups')
            ->where('id', $group_id)
            ->first();

        if (!$group) {
            return SendResponse::badRequest('kesalahan, data tidak dapat ditemukan');
        }

        try {
            DB::beginTransaction();

            $child = DB::table('groups')
                ->where('parent_id', $group->id)
                ->count();
            if ($child > 0) {
                DB::table('groups')
                    ->where('parent_id', $group->id)
                    ->delete();
            }

            DB::table('groups')
                ->where('id', $group->id)
                ->delete();

            DB::commit();
            return SendResponse::accept();
        } catch (\Exception $e) {
            DB::rollBack();
            return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage());
        }
    }
}