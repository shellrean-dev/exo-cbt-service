<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;

use ShellreanDev\Services\Jurusan\JurusanService;

/**
 * Jurusan controller
 * @author shellrean <wandinak17@gmail.com>
 */
class JurusanController extends Controller
{
    /**
     * @Route(path="api/v3/jurusans-all", methods={"GET"})
     */
    public function all(JurusanService $jurusanService)
    {
        $jurusans = $jurusanService->fetchAll();
        return SendResponse::acceptData($jurusans);
    }

    /**
     * @Route(path="api/v3/jurusans", methods={"GET"})
     */
    public function index(Request $request, JurusanService $jurusanService)
    {
        $conditions = $request->get('conditions');
        $conditions = $conditions && is_array($conditions) ? $conditions : [];
        $perPage = intval($request->get('limit'));
        $perPage = $perPage ? $perPage : 10;

        $jurusans = $jurusanService->paginate($conditions, $perPage);
        if (!$jurusans) {
            return SendResponse::internalServerError();
        }

        return SendResponse::acceptData($jurusans);
    }

    /**
     * @Route(path="api/v3/jurusans", methods={"POST"})
     */
    public function store(Request $request, JurusanService $jurusanService)
    {
        $request->validate([
            'nama' => 'required',
        ]);
        
        $available = [
            'nama' => ''
        ];

        $create = $jurusanService->store((object) array_intersect_key($request->all(), $available));
        if (!$create) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($create);
    }

    /**
     * @Route(path="api/v3/jurusans/{id}", methods={"GET"})
     */
    public function show(string $id, JurusanService $jurusanService)
    {
        $find = $jurusanService->find($id);
        if(is_null($find)) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($find);
    }

    /**
     * @Route(path="api/v3/jurusans/{id}", methods={"PUT"})
     */
    public function update(string $id, Request $request, JurusanService $jurusanService)
    {
        $request->validate([
            'nama' => 'required',
        ]);
        $allowed = [
            'id' => '',
            'nama' => ''
        ];
        $request->merge(['id' => $id]);

        $update = $jurusanService->update((object) array_intersect_key($request->all(), $allowed));
        if (is_null($update)) {
            return SendResponse::internalServerError();
        }
        return SendResponse::acceptData($update);
    }

    /**
     * @Route(path="api/v3/jurusans/{id}", methods={"DELETE"})
     */
    public function destroy(string $id, JurusanService $jurusanService)
    {
        $deleted = $jurusanService->destroy($id);
        if (!$deleted) {
            return SendResponse::internalServerError();
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v3/jurusans-delete", methods={"POST"})
     */
    public function deletes(Request $request, JurusanService $jurusanService)
    {
        $request->validate([
            'jurusans_id' => 'required|array'
        ]);
        $ids = $request->jurusans_id;

        $deleted = $jurusanService->deletes($ids);
        if(!$deleted) {
            return SendResponse::internalServerError();
        }
        return SendResponse::accept();
    }
}
