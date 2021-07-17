<?php

namespace App\Http\Controllers\Api\v3;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use App\Models\BanksoalAdaptif;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * BanksoalAdaptifController Controller
 * @author shellrean <wandinak17@gmail.com>
 */
class BanksoalAdaptifController extends Controller
{
    /**
     * @Route(path="api/v3/banksoal-adaptif", methods={"GET"}, middleware={"auth:api"})
     */
    public function index(Request $request)
    {
        # get perpage
        $per_page = $request->query('perPage');
        $per_page = intval($per_page);

        $data = BanksoalAdaptif::with(['matpel' => function($query) {
            $query->select('id', 'nama');
        }])->paginate($per_page);

        return SendResponse::acceptData($data);
    }

    /**
     * @Route(path="api/v3/banksoal-adaptif", methods={"POST"}, middleware={"auth:api"})
     */
    public function store(Request $request)
    {
        $request->validate([
            'matpel_id' => 'required|uuid',
            'code' => 'required',
            'name' => 'required',
            'max_pg' => 'required|numeric'
        ]);

        $data = [
            'matpel_id' => $request->matpel_id,
            'code' => $request->code,
            'name' => $request->name,
            'max_pg' => intval($request->max_pg)
        ];
        
        $data = BanksoalAdaptif::create($data);
        
        return SendResponse::accept('Banksoal adaptif berhasil ditambahkan');
    }

    /**
     * @Route(path="api/v3/banksoal-adaptif/{banksoal_id}", methods={"GET"}, middleware={"auth:api"})
     */
    public function show($banksoal_id)
    {
        $banksoal = BanksoalAdaptif::find($banksoal_id);
        return SendResponse::acceptData($banksoal);
    }

    /**
     * @Route(path="api/v3/banksoal-adaptif/{banksoal_id}", methods={"PUT"}, middleware={"auth:api"})
     */
    public function update(Request $request, $banksoal_id)
    {
        $banksoal = BanksoalAdaptif::findOrFail($banksoal_id);
        if (!$banksoal) {
            return SendResponse::notFound('banksoal adaptif tidak ditemukan');
        }

        $request->validate([
            'matpel_id' => 'required|uuid',
            'code' => 'required',
            'name' => 'required',
            'max_pg' => 'required|numeric'
        ]);

        $banksoal->update([
            'matpel_id' => $request->matpel_id,
            'code' => $request->code,
            'name' => $request->name,
            'max_pg' => $request->max_pg
        ]);
        
        return SendResponse::accept('banksoal berhasil diubah');
    }

    /**
     * @Route(path="api/v3/banksoal-adaptif/{banksoal_id}", methods={"DELETE"}, middleware={"auth:api"})
     */
    public function destroy($banksoal_id)
    {
        $banksoal = BanksoalAdaptif::findOrFail($banksoal_id);
        $banksoal->destroy();

        return SendResponse::accept('banksoal berhasil dihapus');
    }
}