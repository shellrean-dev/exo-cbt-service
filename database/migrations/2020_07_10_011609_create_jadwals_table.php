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
            $table->text('group_ids')->nullable();
            $table->string('alias', 50);
            $table->date('tanggal');
            $table->time('mulai');
            $table->integer('lama');
            $table->integer('min_test')->default(0);
            $table->char('status_ujian',1);
            $table->uuid('event_id')->nullable()->default(null);
            $table->integer('sesi')->default(1);
            $table->text('setting');
            $table->text('mulai_sesi')->nullable();
            $table->integer('view_result')->default(0);

            $table->uuid('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->index(['tanggal', 'status_ujian', 'event_id']);
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
