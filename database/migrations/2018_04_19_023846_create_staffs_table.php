<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('staffs')) {
            Schema::create('staffs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->string('email')->unique();
                $table->unsignedTinyInteger('active')->default(0);
                $table->string('staff_code')->unique()->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('staffs');
    }
}
