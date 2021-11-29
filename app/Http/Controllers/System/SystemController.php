<?php
namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;

final class SystemController extends Controller
{
    public function index()
    {
        return view('system.index');
    }

    public function changeIP()
    {
        return view('system.change_ip');
    }

    public function checkUpdate()
    {
        return redirect(route('system.exo.index'))->with('updated', true);
    }
}