<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiswaUjiansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('siswa_ujians', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('peserta_id');
            $table->bigInteger('jadwal_id');
            $table->string('mulai_ujian');
            $table->integer('uploaded')->default('0');
            $table->integer('sisa_waktu');
            $table->char('status_ujian');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('siswa_ujians');
    }
}
