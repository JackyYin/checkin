<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNoteToStaffProfile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('staff_profile', 'note')) {
            Schema::table('staff_profile', function (Blueprint $table) {
                $table->string('note')->comment('備註')->after('group');
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
        if (Schema::hasColumn('staff_profile', 'note')) {
            Schema::table('staff_profile', function (Blueprint $table) {
                $table->dropColumn('note');
            });
        }
    }
}
