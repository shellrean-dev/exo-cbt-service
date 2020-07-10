<?php

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class AgamaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('agamas')->insert([
            [ 'nama' => 'Islam' ],
            [ 'nama' => 'Protestan' ],
            [ 'nama' => 'Katolik' ],
            [ 'nama' => 'Hindu' ],
            [ 'nama' => 'Budha']
        ]);
    }
}
