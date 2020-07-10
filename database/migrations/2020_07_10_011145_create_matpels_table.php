<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatpelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matpels', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel', 100);
            $table->string('jurusan_id')->nullable();
            $table->integer('agama_id')->nullable()->default(0);
            $table->string('correctors')->nullable();
            $table->string('nama');
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
        Schema::dropIfExists('matpels');
    }
}
