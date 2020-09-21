<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\UjianService;
use App\Actions\SendResponse;
use Illuminate\Http\Request;

class SetUjianController extends Controller
{
    /**
     * Create new ujian
     * 
     * @param  Request $request
     * @return json
     */
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_id'     => 'required|exists:jadwals,id'
        ]);

        $created = UjianService::createNew($request->only('jadwal_id'));
        if(!$created['success']) {
            return SendResponse::badRequest($created['message']);
        }
        return SendResponse::accept($created['message']);
    }
}
