<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MatpelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('matpels')->insert([
        	['kode_mapel' => '00019101', 'jurusan_id' => 0, 'agama_id' => 0, 'correctors' => '[1]', 'nama' => 'Pendidikan Agama'],
        	['kode_mapel' => '00019102', 'jurusan_id' => 0, 'agama_id' => 0, 'correctors' => '[1]', 'nama' => 'Bahasa Indonesia'],
        	['kode_mapel' => '00019103', 'jurusan_id' => 0, 'agama_id' => 0, 'correctors' => '[1]', 'nama' => 'Matematika']
        ]);
    }
}
