<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPasswordToStaffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('staffs', 'password')) {
            Schema::table('staffs', function (Blueprint $table) {
                $table->string('password')->nullable();
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
        if (Schema::hasColumn('staffs', 'password')) {
            Schema::table('staffs', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }
    }
}
