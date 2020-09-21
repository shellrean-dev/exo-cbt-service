<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('banksoal_id');
            $table->integer('tipe_soal')->comment('1: Pilihan Ganda | 2: Esay | 3: Listening');
            $table->longText('pertanyaan');
            $table->longText('rujukan')->nullable();
            $table->string('audio')->nullable();
            $table->string('direction')->nullable();
            $table->longText('analys')->nullable();
            $table->timestamps();

            $table->foreign('banksoal_id')->references('id')->on('banksoals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soals');
    }
}
