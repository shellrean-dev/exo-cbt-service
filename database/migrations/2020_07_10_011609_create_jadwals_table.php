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
            $table->id();
            $table->string('banksoal_id');
            $table->string('alias', 50);
            $table->date('tanggal');
            $table->time('mulai');
            $table->integer('lama');
            $table->char('status_ujian',1);
            $table->unsignedBigInteger('event_id')->default(0);
            $table->integer('sesi')->default(1);
            $table->string('setting');
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
