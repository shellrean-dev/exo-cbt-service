<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banksoals', function (Blueprint $table) {
            $table->id();
            $table->string('kode_banksoal', 100);
            $table->integer('jumlah_soal')->comment('jumlah soal pilihan ganda');
            $table->integer('jumlah_pilihan')->default(4)->comment('jumlah pilihan / opsi pada pilihan ganda');
            $table->integer('jumlah_soal_listening')->default(0)->comment('jumlah soal listening');
            $table->integer('jumlah_pilihan_listening')->default(4)->comment('jumlah opsi listening');
            $table->integer('jumlah_soal_esay')->default(0)->nullable();
            $table->integer('jumlah_soal_ganda_kompleks')->default(0)->comment('jumlah soal pilihan ganda kompleks');
            $table->integer('jumlah_isian_singkat')->default(0)->comment('jumlah isian singkat');
            $table->integer('jumlah_menjodohkan')->default(0)->comment('jumlah menjodohkan');

            $table->string('persen');
            $table->unsignedBigInteger('matpel_id');
            $table->unsignedBigInteger('author');
            $table->unsignedBigInteger('directory_id');

            $table->foreign('matpel_id')->references('id')->on('matpels')->onDelete('cascade');
            $table->foreign('author')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('directory_id')->references('id')->on('directories')->onDelete('cascade');
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
        Schema::dropIfExists('banksoals');
    }
}
