<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\GroupMemberImport;
use App\Actions\SendResponse;
use Illuminate\Http\Request;

class GroupMemberController extends Controller
{
    /**
     * Ambil data member grup
     * 
     * @return \App\Actions\SendResponse
     * @author <wandinak17@gmail.com>
     */
    public function index()
    {
        try {
            $group_id = request()->q;
            $group = DB::table('groups')
                ->where('id', $group_id)
                ->first();

            if (!$group) {
                return SendResponse::badRequest('kesalahan, data yang diminta tidak dapat ditemukan');
            }

            $data = DB::table('group_members');

            if ($group->parent_id == 0) {
                $childs = DB::table('groups')
                    ->where('parent_id', $group->id)
                    ->select('id')
                    ->get()
                    ->pluck('id')
                    ->toArray();
                if (count($childs) > 0) {
                    array_push($childs, $group->id);
                    $data = $data->whereIn('group_id', $childs);
                } else {
                    $data = $data->where('group_id', $group_id);
                }
            } else {
                $data = $data->where('group_id', $group_id);
            }
            $data = $data->join('pesertas', 'group_members.student_id', '=', 'pesertas.id')
                ->select('group_members.id', 'pesertas.nama','pesertas.no_ujian')
                ->get();

            return SendResponse::acceptData($data);
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage());
        }
    }

    /**
     * Buat data member baru
     * 
     * @param \Illuminate\Http\Request $request
     * @return \App\Actions\SendResponse
     * @author <wandinak17@gmail.com>
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_id'      => 'required|exists:groups,id',
            'student_id'    => 'required|exists:pesertas,id'
        ]);

        try {
            DB::table('group_members')->insert([
                'group_id'      => $request->group_id,
                'student_id'    => $request->student_id,
                'created_at'    => now(),
                'updated_at'    => now()
            ]);
            return SendResponse::accept();
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage()); 
        }
    }

    /**
     * Buat multi-data member baru
     * 
     * @param \Illuminate\Http\Request $request
     * @return \App\Actions\SendResponse
     * @author <wandinak17@gmail.com>
     */
    public function multiStore(Request $request)
    {
        $request->validate([
            'group_id'      => 'required|exists:groups,id',
            'no_ujians'     => 'required|array'
        ]);

        try {
            $students = DB::table('pesertas')
                ->whereIn('no_ujian', $request->no_ujians)
                ->select('id')
                ->get();

            $datas = [];
            foreach ($students as $student) {
                array_push($datas, [
                    'group_id'  => $request->group_id,
                    'student_id'    => $student->id,
                ]);
            }

            DB::table('group_members')->insert($datas);
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage());
        }
    }

    /**
     * Import multi-data member 
     * dari excel
     * 
     * @param \Illuminate\Http\Request $request
     * @return \App\Actions\SendResponse;
     * @author <wandinak17@gmail.com>
     */
    public function multiStoreImport(Request $request)
    {
        $request->validate([
            'file'      => 'required|mimes:xlsx,xls',
            'group_id'  => 'required|exists:groups,id'
        ]);

        try {
            Excel::import(new GroupMemberImport($request->group_id), $request->file('file'));
        } catch (\Exception $e) {
            return SendResponse::internalServerError('kesalahan 500.'.$e->getMessage());
        }
    }

    /**
     * Hapus data member
     * 
     * @param int $member_id
     * @return \App\Actions\SendResponse
     * @author <wandinak17@gmail.com>
     */
    public function destroy($member_id)
    {
        $member = DB::table('group_members')
            ->where('id', $member_id)
            ->count();

        if ($member < 1) {
            return SendResponse::badRequest('kesalahan, data yang diminta tidak dapat ditemukan');
        }

        try {
            DB::table('group_members')
                ->where('id', $member_id)
                ->delete();
            return SendResponse::accept();
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage());
        }
    }

    /**
     * Hapus multi-data member
     * 
     * @return \App\Actions\SendResponse
     * @author <wandinak17@gmail.com>
     */
    public function multiDestroy()
    {
        try {
            $str_ids = request()->q;
            $ids = explode(',', $str_ids);
            DB::table('group_members')
                ->whereIn('id', $ids)
                ->delete();
        } catch (\Exception $e) {
            return SendResponse::internalServerError('kesalahan 500.'.$e->getMessage());
        }
    }
}
