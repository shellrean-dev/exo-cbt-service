<?php
namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Dotenv\Result\Success;
use Illuminate\Http\Request;

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

    public function storeChangeIP(Request $request)
    {
        $request->validate([
            'protocol' => 'required|in:http,https',
            'ip_address' => 'required'
        ]);

        $app_url = $request->protocol.'//'.$request->ip_address;
        $this->setEnvironmentValue('APP_URL', $app_url);

        return redirect(route('system.exo.index'))->with('changeip', true);
    }

    public function checkUpdate()
    {
        return redirect(route('system.exo.index'))->with('updated', true);
    }

    private function setEnvironmentValue($envKey, $envValue)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        $str .= "\n";
        $keyPosition = strpos($str, "{$envKey}=");
        $endOfLinePosition = strpos($str, PHP_EOL, $keyPosition);
        $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
        $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
        $str = substr($str, 0, -1);

        $fp = fopen($envFile, 'w');
        fwrite($fp, $str);
        fclose($fp);
    }
}