<?php

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgamaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sclrs = [
            '3aed771a-9458-4cce-9811-8b0b50bfe462',
            '6e4c117b-b057-44a3-98ab-d54d197030de',
            'dae66fe2-5785-4b44-892b-6a40c1c2e1f1',
            '8194f3f2-501b-420f-a496-85fded97beb0',
            'b835ff17-369c-4250-a565-000a06953adf'
        ];

        $sclr_0 = now();
        $timestamps = ['created_at' => $sclr_0, 'updated_at' => $sclr_0];

        DB::table('agamas')->insert(array_map(function($item) use ($timestamps) {
            return array_merge($item, $timestamps);
        },[
            [ 'id' => $sclrs[0], 'kode' => 'ISLAM','nama' => 'Islam' ],
            [ 'id' => $sclrs[1], 'kode' => 'PROTESTAN','nama' => 'Protestan' ],
            [ 'id' => $sclrs[2], 'kode' => 'KATOLIK','nama' => 'Katolik' ],
            [ 'id' => $sclrs[3], 'kode' => 'HINDU','nama' => 'Hindu' ],
            [ 'id' => $sclrs[4], 'kode' => 'BUDHA','nama' => 'Budha']
        ],));
    }
}
