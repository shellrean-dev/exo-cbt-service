<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHasilUjiansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hasil_ujians', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peserta_id');
            $table->unsignedBigInteger('jadwal_id');
            $table->integer('jumlah_salah');
            $table->integer('jumlah_benar');
            $table->float('point_esay');
            $table->integer('tidak_diisi');
            $table->float('hasil');
            $table->longText('jawaban_peserta');
            $table->timestamps();

            $table->foreign('peserta_id')->references('id')->on('pesertas')->onDelete('cascade')->onDelete('cascade');
            $table->foreign('jadwal_id')->references('id')->on('jadwals')->onDelete('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hasil_ujians');
    }
}
