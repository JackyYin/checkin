<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropModuleNameStaffModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('staff_module', 'module_name')) {
            Schema::table('staff_module', function (Blueprint $table) {
                $table->dropColumn('module_name');
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
        if (!Schema::hasColumn('staff_module', 'module_name')) {
            Schema::table('staff_module', function (Blueprint $table) {
                $table->string('module_name');
            });
        }
    }
}
