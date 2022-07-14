<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_reminders', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('loan_id');
            $table->string('installation_date')->nullable();
            $table->string('reminder_date_eng')->nullable();
            $table->string('reminder_date_nep')->nullable();
            $table->text('reminder_detail')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_reminders');
    }
}
