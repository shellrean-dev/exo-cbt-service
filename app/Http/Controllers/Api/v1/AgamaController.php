<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use App\Agama;

/**
 * Agama controller
 * @author shellrean <wandinak17@gmail.com>
 */
class AgamaController extends Controller
{
    /**
     * @Route(path="api/v1/agamas", method={"GET"})
     */
    public function index()
    {
        $agamas = Agama::orderBy('id')->get();
        return SendResponse::acceptData($agamas);
    }
}
