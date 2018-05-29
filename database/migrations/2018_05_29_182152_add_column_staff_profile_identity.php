<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStaffProfileIdentity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff_profile', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_profile', 'identity')) {
                $table->unsignedTinyInteger('identity')->default(0)->after('ID_card_number');
            }
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
            if (Schema::hasColumn('staff_profile', 'identity')) {
                $table->dropColumn('identity');
            }
        });
    }
}
