<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnBots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bots', function (Blueprint $table) {
            if (!Schema::hasColumn('bots', 'auth_hook_url')) {
                $table->string('auth_hook_url')->nullable();
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
        Schema::table('bots', function (Blueprint $table) {
            if (Schema::hasColumn('bots', 'auth_hook_url')) {
                $table->dropColumn('auth_hook_url');
            }
        });
    }
}
