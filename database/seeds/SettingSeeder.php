<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'name'  => 'set_sekolah',
            'value' => [
                'logo' => '',
                'nama_sekolah' => '',
                'email' => '',
                'alamat' => '',
                'kepala_sekolah' => '',
                'nip_kepsek' => '',
                'tingkat' => 'SMK-SMA'
            ],
            'type' => 'sekolah'
        ]);

        Setting::create([
            'name'  => 'token',
            'value' => 900,
            'type' => 'general'
        ]);

        Setting::create([
            'name' => 'ujian',
            'type' => 'general',
            'value' => [
                'reset' => false
            ]
        ]);

        Setting::create([
            'name' => 'user-agent-whitelist',
            'type'  => 'general',
            'value' => '*'
        ]);
    }
}
