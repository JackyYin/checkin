<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffAuthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('staff_auth')) {
            Schema::create('staff_auth', function (Blueprint $table) {
                $table->unsignedInteger('staff_id');
                $table->string('auth_code');

                
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
        Schema::table('staff_auth', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::dropIfExists('staff_auth');
    }
}
