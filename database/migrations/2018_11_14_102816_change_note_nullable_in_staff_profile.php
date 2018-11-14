<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNoteNullableInStaffProfile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff_profile', function (Blueprint $table) {
            $table->string('note')->nullable()->comment('備註')->after('group')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff_profile', function (Blueprint $table) {
            $table->string('note')->comment('備註')->after('group')->change();
        });
    }
}
