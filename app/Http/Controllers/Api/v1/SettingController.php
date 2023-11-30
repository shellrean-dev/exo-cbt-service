<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\Setting;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * SettingController
 * @author shellrean <wandinak17@gmail.com>
 */
class SettingController extends Controller
{
    /**
     * @Route(path="api/v1/settings/sekolah", methods={"GET"})
     *
     * Get setting sekolah
     *
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getSettingSekolah()
    {
        $setting = DB::table('settings')
            ->where('name', 'set_sekolah')
            ->select('id','name','value')
            ->first();
        $settingData = json_decode($setting->value, true);
        if (isset($settingData['logo']) && $settingData['logo'] != '') {
            $settingData['logo'] = sprintf('/storage/%s', $settingData['logo']);
        }
        return SendResponse::acceptData([
            'name'  => $setting->name,
            'value' => $settingData
        ]);
    }

    /**
     * @Route(path="api/v1/settings-public-sekolah", methods={"GET"})
     *
     * Get setting sekolah public
     *
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getSettingPublicSekolah()
    {
        $setting = DB::table('settings')
            ->where('name', 'set_sekolah')
            ->select('id','name','value')
            ->first();

        $value = json_decode($setting->value, true);
        $sekolah_name = isset($value['nama_sekolah']) ? $value['nama_sekolah'] : '';
        $logo = isset($value['logo']) && $value['logo'] != '' ? sprintf('/storage/%s', $value['logo']) : '';

        return SendResponse::acceptData([
            'sekolah_name'  => $sekolah_name,
            'logo' => $logo
        ]);
    }

    /**
     * @Route(path="api/v1/settings/sekolah", methods={"POST"})
     *
     * Store setting sekolah
     *
     * @param Illuminate\Http\Request $request
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function storeSettingSekolah(Request $request)
    {
        $request->validate([
            'nama_sekolah'      => 'required',
            'email'         => 'required|email',
            'alamat'        => 'required',
            'kepala_sekolah' => 'required',
            'tingkat' => 'required'
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
                'nip_kepsek' => $request->nip_kepsek,
                'tingkat' => $request->tingkat
            ];
            $sekolah->save();
        } else {
            $sekolah = Setting::create([
                'name'  => 'set_sekolah',
                'value' => [
                    'logo' => '',
                    'nama_sekolah'  => $request->nama_sekolah,
                    'email' => $request->email,
                    'alamat'    => $request->alamat,
                    'kepala_sekolah' => $request->kepala_sekolah,
                    'nip_kepsek' => $request->nip_kepsek,
                    'tingkat' => $request->tingkat,
                ],
                'type' => 'sekolah'
            ]);
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/settings/sekolah/logo", methods={"POST"})
     *
     * Change logo sekolah
     *
     * @param  Illuminate\Httpp\Request $request
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function changeLogoSekolah(Request $request)
    {
        $request->validate([
            'image' => 'required|mimes:png,jpg,jpeg'
        ]);
        try {
            $file = $request->file('image');
            $filename = Str::uuid()->toString() .'.webp';
            $path = $filename;

            $image = Image::make($file)->encode('webp', 90);
            Storage::put($path, $image->__toString());
            
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
     * @Route(path="api/v1/settings", methods={"GET"})
     *
     * Get data setting
     *
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getSetting()
    {
        if(isset(request()->setting) && !empty(request()->setting)) {
            $setting = request()->setting;

            $sett = DB::table('settings')
                ->where('name', $setting)
                ->select('id','name','value')
                ->first();
            if($sett) {
                return SendResponse::acceptData([
                    'name'  => $sett->name,
                    'value' => json_decode($sett->value, true),
                ]);
            }
        }
        return SendResponse::acceptData([
            'name'  => request()->setting,
            'value' => []
        ]);
    }

    /**
     * @Route(path="api/v1/settings", methods={"POST"})
     *
     * Store data setting
     *
     * @param Illuminate\Http\Request $request
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
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

    /**
     * @Route(path="api/v1/settings/auth", methods={"GET"})
     *
     * Get setting authentication method
     *
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
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

    /**
     * @Route(path="api/v1/info-app", methods={"GET"})
     *
     * Get app info
     *
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function infoApp()
    {
        return response()->json([
            'name'      => 'Extraordinary CBT',
            'version' => config('exo.version.code'),
            'code' => config('exo.version.name'),
            'author'    => 'shellrean',
            'email'     => '<wandinak17@gmail.com>'
        ]);
    }
}
