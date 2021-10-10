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
            $table->uuid('id')->primary();
            $table->uuid('banksoal_id');
            $table->uuid('peserta_id');
            $table->uuid('jadwal_id');
            $table->integer('jumlah_salah')->default(0);
            $table->integer('jumlah_benar')->default(0);
            $table->integer('jumlah_benar_complek')->default(0);
            $table->integer('jumlah_salah_complek')->default(0);
            $table->integer('jumlah_benar_listening')->default(0);
            $table->integer('jumlah_salah_listening')->default(0);
            $table->integer('jumlah_benar_isian_singkat')->default(0);
            $table->integer('jumlah_salah_isian_singkat')->default(0);
            $table->integer('jumlah_benar_menjodohkan')->default(0);
            $table->integer('jumlah_salah_menjodohkan')->default(0);
            $table->integer('jumlah_benar_mengurutkan')->default(0);
            $table->integer('jumlah_salah_mengurutkan')->default(0);
            $table->float('point_esay');
            $table->integer('tidak_diisi');
            $table->float('hasil');
            $table->timestamps();

            $table->foreign('peserta_id')->references('id')->on('pesertas')->onDelete('cascade');
            $table->foreign('banksoal_id')->references('id')->on('banksoals')->onDelete('cascade');
            $table->foreign('jadwal_id')->references('id')->on('jadwals')->onDelete('cascade');
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
