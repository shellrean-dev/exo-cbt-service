<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJadwalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jadwals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('banksoal_id');
            $table->text('group_id')->nullable();
            $table->string('alias', 50);
            $table->date('tanggal');
            $table->time('mulai');
            $table->integer('lama');
            $table->char('status_ujian',1);
            $table->uuid('event_id')->nullable()->default(null);
            $table->integer('sesi')->default(1);
            $table->string('setting');
            $table->string('mulai_sesi')->default('{}');
            $table->integer('view_result')->default(0);
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
        Schema::dropIfExists('jadwals');
    }
}
