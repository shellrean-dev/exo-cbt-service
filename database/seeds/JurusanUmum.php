<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JurusanUmum extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('jurusans')->insert([
           'id' => Str::uuid()->toString(),
           'kode' => 1945,
           'nama' => 'UMUM (tanpa-jurusan)'
        ]);
    }
}
