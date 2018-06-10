<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistrationTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('registration_token')) {
            Schema::create('registration_token', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('staff_id');
                $table->string('token');
                
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
        Schema::table('registration_token', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::dropIfExists('registration_token');
    }
}
