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
            $table->uuid('id')->primary();
            $table->string('kode_banksoal', 100);
            $table->integer('jumlah_soal')->comment('jumlah soal pilihan ganda');
            $table->integer('jumlah_pilihan')->default(4)->comment('jumlah pilihan / opsi pada pilihan ganda');
            $table->integer('jumlah_soal_listening')->default(0)->comment('jumlah soal listening');
            $table->integer('jumlah_pilihan_listening')->default(4)->comment('jumlah opsi listening');
            $table->integer('jumlah_soal_esay')->default(0)->nullable();
            $table->integer('jumlah_soal_ganda_kompleks')->default(0)->comment('jumlah soal pilihan ganda kompleks');
            $table->integer('jumlah_isian_singkat')->default(0)->comment('jumlah isian singkat');
            $table->integer('jumlah_menjodohkan')->default(0)->comment('jumlah menjodohkan');
            $table->integer('jumlah_mengurutkan')->default(0)->comment('jumlah mengurutkan');
            $table->integer('jumlah_benar_salah')->default(0)->comment('jumlah benar-salah');
            $table->integer('jumlah_setuju_tidak')->default(0)->comment('jumlah setuju-tidak');

            $table->integer('is_locked')->default(0);
            $table->string('key_lock')->nullable();
            $table->uuid('lock_by')->nullable();
            $table->string('persen');
            $table->uuid('matpel_id');
            $table->uuid('author');
            $table->uuid('directory_id');

            $table->foreign('matpel_id')->references('id')->on('matpels')->onDelete('cascade');
            $table->foreign('author')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('directory_id')->references('id')->on('directories')->onDelete('cascade');

            $table->uuid('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['kode_banksoal', 'matpel_id', 'author', 'directory_id']);
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
