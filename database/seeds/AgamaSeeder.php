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
        DB::table('agamas')->insert([
            [ 'id' => Str::uuid()->toString(), 'nama' => 'Islam' ],
            [ 'id' => Str::uuid()->toString(), 'nama' => 'Protestan' ],
            [ 'id' => Str::uuid()->toString(), 'nama' => 'Katolik' ],
            [ 'id' => Str::uuid()->toString(), 'nama' => 'Hindu' ],
            [ 'id' => Str::uuid()->toString(), 'nama' => 'Budha']
        ]);
    }
}
