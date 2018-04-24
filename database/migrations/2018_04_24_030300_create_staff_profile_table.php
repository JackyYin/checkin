<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_profile', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('staff_id');
            $table->string('staff_code')->unique()->nullable();
            $table->string('name', 50);
            $table->string('ID_card_number', 10)->unique();
            $table->unsignedTinyInteger('gender')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->unique();
            $table->string('home_address')->nullable()->comment('戶籍地址');
            $table->string('mailing_address')->nullable()->comment('通訊地址');
            $table->string('bank_account')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('position')->nullable()->comment('職稱');
            $table->date('on_board_date')->nullable()->comment('到職日');
            $table->date('off_board_date')->nullable()->comment('離職日');
            $table->date('birth')->nullable();
            $table->unsignedInteger('salary')->nullable();
            $table->date('add_insurance_date')->nullable()->comment('加保日期');
            $table->date('cancel_insurance_date')->nullable()->comment('退保日期');
            $table->string('highest_education')->nullable()->comment('最高學歷');
            $table->string('experience')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();

            $table->foreign('staff_id')
                ->references('id')->on('staffs')
                ->onDelete('cascade');
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
            $table->dropForeign(['staff_id']);
        });

        Schema::dropIfExists('staff_profile');
    }
}
