<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateRoleMenus extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menuses')->insert([
            ['code' => '00001A', 'name' => 'Edit instansi'],
            ['code' => '00002A', 'name' => 'Master peserta'],
            ['code' => '00003A', 'name' => 'Master group-peserta'],
            ['code' => '00004A', 'name' => 'Master jurusan'],
            ['code' => '00005A', 'name' => 'Master matpel'],
            ['code' => '00006A', 'name' => 'Master user'],
            ['code' => '00007A', 'name' => 'Master agama'],
            ['code' => '00008A', 'name' => 'Banksoal'],
            ['code' => '00009A', 'name' => 'Event ujian'],
            ['code' => '00010A', 'name' => 'Jadwal ujian'],
            ['code' => '00011A', 'name' => 'File media'],
            ['code' => '00012A', 'name' => 'Status ujian'],
            ['code' => '00013A', 'name' => 'Status peserta'],
            ['code' => '00014A', 'name' => 'Status login peserta'],
            ['code' => '00015A', 'name' => 'Blocked peserta'],
            ['code' => '00016A', 'name' => 'Koreksi esay'],
            ['code' => '00017A', 'name' => 'Koreksi argumen'],
            ['code' => '00018A', 'name' => 'Capaian peserta'],
            ['code' => '00019A', 'name' => 'Kesulitan soal'],
            ['code' => '00020A', 'name' => 'Hasil ujian'],
            ['code' => '00021A', 'name' => 'Ledger peserta'],
            ['code' => '00022A', 'name' => 'Setting'],
            ['code' => '00023A', 'name' => 'Backup & restore']
        ]);

        DB::table('role_menuses')->insert([
            ['role' => 'admin', 'code' => '00001A'],
            ['role' => 'admin', 'code' => '00002A'],
            ['role' => 'admin', 'code' => '00003A'],
            ['role' => 'admin', 'code' => '00004A'],
            ['role' => 'admin', 'code' => '00005A'],
            ['role' => 'admin', 'code' => '00006A'],
            ['role' => 'admin', 'code' => '00007A'],
            ['role' => 'admin', 'code' => '00008A'],
            ['role' => 'admin', 'code' => '00009A'],
            ['role' => 'admin', 'code' => '00010A'],
            ['role' => 'admin', 'code' => '00011A'],
            ['role' => 'admin', 'code' => '00012A'],
            ['role' => 'admin', 'code' => '00013A'],
            ['role' => 'admin', 'code' => '00014A'],
            ['role' => 'admin', 'code' => '00015A'],
            ['role' => 'admin', 'code' => '00016A'],
            ['role' => 'admin', 'code' => '00017A'],
            ['role' => 'admin', 'code' => '00018A'],
            ['role' => 'admin', 'code' => '00019A'],
            ['role' => 'admin', 'code' => '00020A'],
            ['role' => 'admin', 'code' => '00021A'],
            ['role' => 'admin', 'code' => '00022A'],
            ['role' => 'admin', 'code' => '00023A'],
        ]);

        DB::table('role_menuses')->insert([
            ['role' => 'guru', 'code' => '00008A'],
            ['role' => 'guru', 'code' => '00009A'],
            ['role' => 'guru', 'code' => '00010A'],
            ['role' => 'guru', 'code' => '00011A'],
            ['role' => 'guru', 'code' => '00012A'],
            ['role' => 'guru', 'code' => '00013A'],
            ['role' => 'guru', 'code' => '00014A'],
            ['role' => 'guru', 'code' => '00016A'],
            ['role' => 'guru', 'code' => '00017A'],
            ['role' => 'guru', 'code' => '00018A'],
            ['role' => 'guru', 'code' => '00019A'],
            ['role' => 'guru', 'code' => '00020A'],
        ]);

        DB::table('role_menuses')->insert([
            ['role' => 'operator', 'code' => '00009A'],
            ['role' => 'operator', 'code' => '00010A'],
            ['role' => 'operator', 'code' => '00011A'],
            ['role' => 'operator', 'code' => '00012A'],
            ['role' => 'operator', 'code' => '00013A'],
            ['role' => 'operator', 'code' => '00014A'],
            ['role' => 'operator', 'code' => '00015A'],
        ]);
    }
}
