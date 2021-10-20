<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\GroupMemberImport;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * GroupMemberController
 * @author shellrean <wandinak17@gmail.com>
 */
class GroupMemberController extends Controller
{
    /**
     * @Route(path="api/v1/group_members", methods={"GET"})
     *
     * Ambil data member grup
     *
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
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
                ->paginate(50);

            return SendResponse::acceptData($data);
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage());
        }
    }

    /**
     * @Route(path="api/v1/group_members", methods={"POST"})
     *
     * Buat data member baru
     *
     * @param Request $request
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_id'      => 'required|exists:groups,id',
            'student_id'    => 'required|exists:pesertas,id'
        ]);

        try {
            DB::table('group_members')->insert([
                'id'            => Str::uuid()->toString(),
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
     * @Route(path="api/v1/group_members/multiple", methods={"POST"})
     *
     * Buat multi-data member baru
     *
     * @param Request $request
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
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

            $exists = DB::table('group_members')
                ->whereIn('student_id', $students->pluck('id')->toArray())
                ->join('pesertas', 'pesertas.id', '=', 'group_members.student_id')
                ->select(['pesertas.no_ujian'])
                ->get();
            if (count($exists)) {
                return SendResponse::badRequest('No ujian '.$exists->pluck('no_ujian')->values().' telah terdaftar di group');
            }

            $datas = [];
            foreach ($students as $student) {
                array_push($datas, [
                    'id'    => Str::uuid()->toString(),
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
     * @Route(path="api/v1/group_members/import", methods={"POST"})
     *
     * Import multi-data member
     * dari excel
     *
     * @param Request $request
     * @return Response;
     * @author shellrean <wandinak17@gmail.com>
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
     * @Route(path="api/v1/group_members/{member_id}", methods={"DELETE"})
     *
     * Hapus data member
     *
     * @param string $member_id
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
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
     * @Route(path="api/v1/group_members/multiple", methods={"DELETE"})
     *
     * Hapus multi-data member
     *
     * @return  Response
     * @author shellrean <wandinak17@gmail.com>
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
