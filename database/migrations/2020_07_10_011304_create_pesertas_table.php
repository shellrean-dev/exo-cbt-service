<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePesertasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pesertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('sesi');
            $table->string('no_ujian', 50)->unique();
            $table->uuid('agama_id');
            $table->uuid('jurusan_id', 10);
            $table->string('nama');
            $table->string('password');
            $table->string('api_token')->nullable();
            $table->integer('status')->default(1);
            $table->string('block_reason')->nullable();
            $table->boolean('antiblock')->default(false);

            $table->uuid('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['no_ujian', 'api_token']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pesertas');
    }
}
