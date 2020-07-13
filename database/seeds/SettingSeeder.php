<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            'name'  => 'set_sekolah',
            'value' => [
                'logo' => '',
                'nama_sekolah' => '',
                'email' => '',
                'alamat' => '',
                'kepala_sekolah' => '',
                'nip_kepsek' => ''
            ],
            'type' => 'sekolah'
        ]);
    }
}
