<?php

namespace App\Http\Controllers\Api\v3;

use ShellreanDev\Services\Matpel\MatpelService;
use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use App\Matpel;
use Illuminate\Http\Request;
use App\Rules\ArrayUuid;

/**
 * Matpel controller
 * @author shellrean <wandinak17@gmail.com>
 */
class MatpelController extends Controller
{
    /**
     * @Route(path="api/v3/matpels-all", methods={"GET"})
     */
    public function all(MatpelService $matpelService)
    {
        $all = $matpelService->fetchAll();
        if(is_null($all)) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($all);
    }

    /**
     * @Route(path="api/v3/matpels-delete", methods={"POST"})
     */
    public function deletes(Request $request, MatpelService $matpelService)
    {
        $request->validate([
            'matpel_id' => ['required', new ArrayUuid],
        ]);

        $deleted = $matpelService->deletes($request->matpel_id);
        if (!$deleted) {
            return SendResponse::internalServerError();
        }
        return SendResponse::accept('matpel yang dipilih berhasil dihapus');
    }

    /**
     * @Route(path="api/v3/matpels-import", methods={"POST"})
     */
    public function import(Request $request, MatpelService $matpelService)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $import = $matpelService->import($request->file('file'));
        if (!$import) {
            return SendResponse::internalServerError();
        }
        return SendResponse::accept('import excel matpel sukses');
    }

    /**
     * @Route(path="api/v3/matpels", methods={"GET"})
     */
    public function index(Request $request, MatpelService $matpelService)
    {
        $limit = intval($request->limit) ?: 10;
        $conditions = is_array($request->conditions);
        $conditions = $conditions ? $conditions : [];

        $paginate = $matpelService->paginate($conditions, $limit);
        if (!$paginate) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($paginate);
    }

    /**
     * @Route(path="api/v3/matpels", methods={"POST"})
     */
    public function store(Request $request, MatpelService $matpelService)
    {
        $request->validate([
            'kode_mapel' => 'required|unique:matpels,kode_mapel',
            'nama' => 'required',
            'jurusan_id' => 'array',
            'correctors' => 'array'
        ]);

        $allowed = [
            'kode_mapel' => '',
            'nama' => '',
            'jurusan_id' => '',
            'agama_id' => '',
            'correctors' => ''
        ];

        $request->merge([
            'jurusan_id' => ($request->jurusan_id != '' ? array_column($request->jurusan_id, 'id') : 0 ),
            'correctors' => ($request->correctors != '' ? array_column($request->correctors, 'id') : 0 ),
            'agama_id' => ($request->agama_id != '' ? $request->agama_id : 0)
        ]);

        $store = $matpelService->store((object) array_intersect_key($request->all(), $allowed));
        if (!$store) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($store);
    }

    /**
     * @Route(path="api/v3/matpels/{id}", methods={"PUT"})
     */
    public function update(string $id, Request $request, MatpelService $matpelService)
    {
        $request->all([
            'kode_mapel' => 'required|unique:matpels,kode_mapel,'.$id,
            'nama' => 'required',
            'jurusan_id' => 'array',
            'correctors' => 'array'
        ]);

        $allowed = [
            'id' => '',
            'kode_mapel' => '',
            'nama' => '',
            'jurusan_id' => '',
            'agama_id' => '',
            'correctors' => ''
        ];
        
        $request->merge([
            'id' => $id,
            'jurusan_id' => ($request->jurusan_id != '' ? array_column($request->jurusan_id, 'id') : 0 ),
            'correctors' => ($request->correctors != '' ? array_column($request->correctors, 'id') : 0 ),
            'agama_id' => ($request->agama_id != '' ? $request->agama_id : 0)
        ]);
        
        $update = $matpelService->update((object) array_intersect_key($request->all(), $allowed));
        if (!$update) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($update);
    }

    /**
     * @Route(path="api/v3/matpels/{id}", methods={"GET"})
     */
    public function show(string $id, MatpelService $matpelService)
    {
        $show = $matpelService->findOne($id);
        if(!$show) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($show);
    }
}