<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBotStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('bot_staff')) {
            Schema::create('bot_staff', function (Blueprint $table) {
                $table->unsignedInteger('bot_id');
                $table->unsignedInteger('staff_id');
                $table->string('email_auth_token')->nullable();

                $table->foreign('bot_id')
                    ->references('id')->on('bots')
                    ->onDelete('cascade'); 
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
        Schema::dropIfExists('bot_staff');
    }
}
