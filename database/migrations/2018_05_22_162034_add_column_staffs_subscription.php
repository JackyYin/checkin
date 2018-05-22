<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStaffsSubscription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('staffss', 'subscribed')) {
                $table->unsignedTinyInteger('subscribed')->default(0)->comment('是否訂閱請假推播')->after('staff_code');
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
        Schema::table('staffs', function (Blueprint $table) {
            if (Schema::hasColumn('staffs', 'subscribed')) {
                $table->dropColumn('subscribed');
            }
        });
    }
}
