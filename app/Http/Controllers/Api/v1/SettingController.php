<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\Setting;

class SettingController extends Controller
{
    /**
     * [allSetting description]
     * @return [type] [description]
     */
    public function getSettingSekolah()
    {
        $setting = Setting::where('name','set_sekolah')->first();
        return SendResponse::acceptData($setting);
    }

    public function storeSettingSekolah(Request $request)
    {
        $request->validate([
            'nama_sekolah'      => 'required',
            'email'         => 'required|email',
            'alamat'        => 'required',
            'kepala_sekolah' => 'required'
        ]);

        $sekolah = Setting::where('name', 'set_sekolah')->first();
        
        if($sekolah) {
            $sekolah->value = [
                'nama_sekolah'  => $request->nama_sekolah,
                'email' => $request->email,
                'alamat'    => $request->alamat,
                'kepala_sekolah' => $request->kepala_sekolah,
                'nip_kepsek' => $request->nip_kepsek
            ];
            $sekolah->save();
        } else {
            Setting::create([
                'name'  => 'set_sekolah',
                'value' => [
                    'nama_sekolah'  => $request->nama_sekolah,
                    'email' => $request->email,
                    'alamat'    => $request->alamat,
                    'kepala_sekolah' => $request->kepala_sekolah,
                    'nip_kepsek' => $request->nip_kepsek
                ],
                'type' => 'sekolah'
            ]); 
        }

        SendResponse::accept();
    }
}
