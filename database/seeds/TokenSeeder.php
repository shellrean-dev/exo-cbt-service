<?php

use Illuminate\Database\Seeder;
use App\Token;

class TokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Token::create([
        	'token'		=> 'ZYENG',
        	'status'	=> '0'
        ]);
    }
}
