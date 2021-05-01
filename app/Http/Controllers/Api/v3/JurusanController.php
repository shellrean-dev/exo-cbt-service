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
