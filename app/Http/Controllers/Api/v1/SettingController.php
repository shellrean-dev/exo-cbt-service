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

    /**
     * [storeSettingSekolah description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
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
            if(isset($sekolah->value['logo']) && $sekolah->value['logo'] != '') {
                $logo = $sekolah->value['logo'];
            } else {
                $logo = '';
            }
            $sekolah->value = [
                'logo'  => $logo,
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
                    'logo' => '',
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

    /**
     * [changeLogoSekolah description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function changeLogoSekolah(Request $request)
    {
        try {
            $file = $request->file('image');
            $filename = date('d_m_Y_his').'-'.$file->getClientOriginalName();
            $file->storeAs('public', $filename);

            $sekolah = Setting::where('name', 'set_sekolah')->first();
            if($sekolah) {
                $value = $sekolah->value;
                $value['logo'] = $filename;

                $sekolah->value = $value;
                $sekolah->save();
            }
        } catch (\Exception $e) {
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }
}
