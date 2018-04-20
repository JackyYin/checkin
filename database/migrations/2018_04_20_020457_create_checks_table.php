<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('checks')) {
            Schema::create('checks', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('staff_id');
                $table->dateTime('checkin_at');
                $table->dateTime('checkout_at')->nullable();
                $table->unsignedTinyInteger('type')->default(0);
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
        Schema::dropIfExists('checks');
    }
}
