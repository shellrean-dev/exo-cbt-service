<?php

namespace App\Http\Controllers\Api\v3;

use ShellreanDev\Services\Matpel\MatpelService;
use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
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
}
