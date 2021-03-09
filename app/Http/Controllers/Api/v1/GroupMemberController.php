<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
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
            $data = DB::table('group_members')
                ->where('group_id', $group_id)
                ->join('pesertas', 'group_members.student_id', '=', 'pesertas.id')
                ->select('group_members.id', 'pesertas.nama','pesertas.no_ujian')
                ->paginate(50);

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
}
