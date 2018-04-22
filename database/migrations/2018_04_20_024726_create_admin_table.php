<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('admin')) {
            Schema::create('admin', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('staff_id');
                $table->string('name', 10);
                $table->string('email')->unique();
                $table->string('password');
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
        Schema::table('admin', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::dropIfExists('admin');
    }
}
