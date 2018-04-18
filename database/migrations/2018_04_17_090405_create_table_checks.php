<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableChecks extends Migration
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
                $table->unsignedInteger('user_id');
                $table->dateTime('checkin_at');
                $table->dateTime('checkout_at')->nullable();
                $table->unsignedTinyInteger('hours')->nullable()->comment('工時');
                $table->unsignedTinyInteger('status')->default(2)->comment('0:正常;1:沒上班;2:沒下班');
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
