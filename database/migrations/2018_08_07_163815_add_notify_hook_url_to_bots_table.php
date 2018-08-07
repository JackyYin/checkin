<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotifyHookUrlToBotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('bots', 'notify_hook_url')) {
            Schema::table('bots', function (Blueprint $table) {
                $table->string('notify_hook_url')->nullable();
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
        if (Schema::hasColumn('bots', 'notify_hook_url')) {
            Schema::table('bots', function (Blueprint $table) {
                $table->dropColumn('notify_hook_url');
            });

        }
    }
}
