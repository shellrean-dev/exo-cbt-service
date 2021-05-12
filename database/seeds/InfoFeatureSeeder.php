<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InfoFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Info untuk apa kegunaan daripada event
         */
        DB::table('feature_infos')->insert([
            'name' => 'form_setting_ujian_event',
            'code' => 'v1',
            'content' => '<p>Biasanya kita mengadakan ujian</p>
            
            <ul>
                <li>UH (Ulangan Harian)</li>
                <li>UTS (Ulangan Tengah Semester)</li>
            </ul>
            
            <p>inilah kita sebut &#39;event&#39; <br /><br />
            Bila kolom ini diisi, ujian akan menginduk kepada event yang kita buat pada menu <u>ujian </u>&gt; <u>event ujian</u><br /><br />
            Kelebihan menggunakan event adalah</p>
            
            <ol>
                <li>Ubah setting sesi siswa tiap-tiap event sesuai kebutuhan.</li>
                <li>Jadwal dalam kartu ujian siswa</li>
                <li>Absensi</li>
                <li>Berita Acara</li>
                <li>dll</li>
            </ol>
            
            <p>Pada ujian yang menginduk kepada event, sesi ujian siswa harus di setting pada menu event. Sesi pada event dapat berbeda dengan sesi default<br />
            Kosongkan kolom ini sehingga sesi tetap menggunakan default<br />
            <br />
            ExtraordinaryCBT</p>'
        ]);
    }
}
