<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveReasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('leave_reason')) {
            Schema::create('leave_reason', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('check_id');
                $table->string('reason');

                $table->foreign('check_id')
                    ->references('id')->on('checks')
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
        Schema::table('leave_reason', function (Blueprint $table) {
            $table->dropForeign(['check_id']);
        });
        Schema::dropIfExists('leave_reason');
    }
}
