<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJawabanPesertasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jawaban_pesertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('banksoal_id');
            $table->uuid('soal_id');
            $table->uuid('peserta_id');
            $table->uuid('jadwal_id');
            $table->string('jawab')->default(0);
            $table->string('jawab_complex')->nullable()->default("[]");
            $table->text('menjodohkan')->nullable();
            $table->text('mengurutkan')->nullable();
            $table->text('benar_salah')->nullable();
            $table->longText('setuju_tidak')->nullable();
            $table->longText('esay')->nullable();
            $table->char('ragu_ragu',1)->default(0);
            $table->char('iscorrect',1)->default(0);
            $table->boolean('answered')->default(false);

            $table->uuid('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('banksoal_id')->references('id')->on('banksoals')->onDelete('cascade');
            $table->foreign('soal_id')->references('id')->on('soals')->onDelete('cascade');
            $table->foreign('peserta_id')->references('id')->on('pesertas')->onDelete('cascade');
            $table->foreign('jadwal_id')->references('id')->on('jadwals')->onDelete('cascade');

            $table->index(['banksoal_id', 'soal_id', 'peserta_id', 'jadwal_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jawaban_pesertas');
    }
}
