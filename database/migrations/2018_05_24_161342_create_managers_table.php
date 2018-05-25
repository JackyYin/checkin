<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManagersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('managers')) {
            Schema::create('managers', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('staff_id');
                $table->string('name', 50);
                $table->string('email')->unique();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
                $table->foreign('staff_id')
                    ->references('id')->on('staffs')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('managers', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::dropIfExists('managers');
    }
}
