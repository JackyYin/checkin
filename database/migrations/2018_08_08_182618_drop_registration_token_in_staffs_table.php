<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropRegistrationTokenInStaffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('staffs', 'registration_token')) {
            Schema::table('staffs', function (Blueprint $table) {
                $table->dropColumn('registration_token');
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
        if (!Schema::hasColumn('staffs', 'registration_token')) {
            Schema::table('staffs', function (Blueprint $table) {
                $table->string('registration_token')->nullable();
            });
        }
    }
}
