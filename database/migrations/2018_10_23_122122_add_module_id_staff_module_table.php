<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModuleIdStaffModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('staff_module', 'module_id')) {
            Schema::table('staff_module', function (Blueprint $table) {
                $table->unsignedInteger('module_id');

                $table->foreign('module_id')
                    ->references('id')->on('modules')
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
        if (Schema::hasColumn('staff_module', 'module_id')) {
            Schema::table('staff_module', function (Blueprint $table) {
                $table->dropForeign('staff_module_module_id_foreign');
                $table->dropColumn('module_id');
            });
        }
    }
}

