<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExoBackupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exo_backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('version');
            $table->text('detail');
            $table->string('generated_date');
            $table->string('bak_type')->default('BACKUP');
            $table->string('status')->default("SUCCESS");
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
        Schema::dropIfExists('exo_backups');
    }
}
