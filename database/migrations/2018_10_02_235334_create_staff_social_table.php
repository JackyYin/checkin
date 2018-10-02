<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffSocialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('staff_social')) {
            Schema::create('staff_social', function (Blueprint $table) {
                $table->unsignedInteger('staff_id');
                $table->string('provider_user_id');
                $table->string('provider');
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
        Schema::dropIfExists('staff_social');
    }
}
