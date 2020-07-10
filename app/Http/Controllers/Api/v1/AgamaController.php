<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\SendResponse;
use App\Agama;

class AgamaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function index()
    {
        $agamas = Agama::orderBy('id')->get();
        return SendResponse::acceptData($agamas);
    }
}
