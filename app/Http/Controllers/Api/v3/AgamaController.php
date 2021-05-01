<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;

use ShellreanDev\Services\Agama\AgamaService;

/**
 * Agama controller
 * @author shellrean <wandinak17@gmail.com>
 */
class AgamaController extends Controller
{
    /**
     * @Route(path="api/v3/agamas", method={"GET"})
     * 
     * @param AgamaService $agamaService
     * @return SendResponse
     */
    public function index(AgamaService $agamaService)
    {
        $agamas = $agamaService->fetchAll();
        return SendResponse::acceptData($agamas ? $agamas : []);
    }
}