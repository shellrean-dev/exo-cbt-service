<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJawabanEsaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penilaian_esay', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('banksoal_id');
            $table->uuid('peserta_id');
            $table->uuid('jawab_id');
            $table->uuid('corrected_by');
            $table->float('point');

            $table->uuid('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('banksoal_id')->references('id')->on('banksoals')->onDelete('cascade');
            $table->foreign('peserta_id')->references('id')->on('pesertas')->onDelete('cascade');
            $table->foreign('jawab_id')->references('id')->on('jawaban_pesertas')->onDelete('cascade');
            $table->foreign('corrected_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['banksoal_id', 'peserta_id', 'jawab_id', 'corrected_by'], 'penilaian_esay_indexees0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penilaian_esay');
    }
}
