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
            $table->uuid('id')->primary();
            $table->uuid('peserta_id');
            $table->uuid('jadwal_id');
            $table->string('mulai_ujian');
            $table->string('mulai_ujian_shadow');
            $table->string('selesai_ujian')->nullable();
            $table->integer('uploaded')->default('0');
            $table->integer('sisa_waktu');
            $table->char('status_ujian');
            $table->integer('out_ujian_counter')->default(0);
            $table->uuid('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('peserta_id')->references('id')->on('pesertas')->onDelete('cascade');
            $table->foreign('jadwal_id')->references('id')->on('jadwals')->onDelete('cascade');

            $table->index(['peserta_id', 'jadwal_id', 'status_ujian']);
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
