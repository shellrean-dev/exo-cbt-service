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

    /**
     * [getSetting description]
     * @return [type] [description]
     * 
     */
    public function getSetting()
    {
        if(isset(request()->setting) && !empty(request()->setting)) {
            $setting = request()->setting;

            $sett = Setting::where('name', $setting)->first();
            if($sett) {
                return SendResponse::acceptData($sett);
            }
        }
        return SendResponse::acceptData([
            'name'  => request()->setting,
            'value' => []
        ]);
    }

    /**
     * [setSetting description]
     * @param Request $request [description]
     */
    public function setSetting(Request $request)
    {
        $request->validate([
            'name'      => 'required',
            'value'     => 'required'
        ]);

        $setting = Setting::where('name', $request->name)->first();
        if(!$setting) {
            Setting::create([
                'name'  => $request->name,
                'value' => $request->value,
                'type'  => $request->type
            ]);

            return SendResponse::accept();
        }
        $setting->update($request->only('name','value','type'));
        return SendResponse::accept();
    }

    public function getSetAuth()
    {
        $settings = Setting::where('type', 'auth')->get();
        $set = $settings->map(function($item) {
            return [
                'name'  => $item->name,
                'isactive' => $item->value['isactive']
            ];
        })->reject(function($item) {
            return $item['isactive'] == 0;
        });
        return SendResponse::acceptData($set->values()->all());
    }

    public function infoApp()
    {
        return response()->json([
            'name'      => 'Extraordinary CBT',
            'author'    => 'shellrean',
            'email'     => 'wandinak17@gmail.com'
        ]);
    }
}
