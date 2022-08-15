<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('loans', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->integer('loan_type')->default(0);
            $table->double('loan_amount',10,2)->default(0);
            $table->double('total_loan_amount',10,2)->default(0);
            $table->double('emi',10,2)->default(0);
            $table->double('paid_amount',10,2)->default(0);
            $table->double('remaining_amount',10,2)->default(0);
            $table->string('loan_duration')->default(0);
            $table->double('remaining_duration')->default(0);
            $table->string('loan_purpose')->nullable();
            $table->string('installation_type')->nullable();
            $table->string('recommend_to')->nullable();
            $table->string('issue_date_eng')->nullable();
            $table->string('issue_date_nep')->nullable();
            $table->string('due_date_eng')->nullable();
            $table->string('due_date_nep')->nullable();
            $table->integer('user_id')->nullable();
            $table->float('intrest_rate')->default(0);
            $table->double('intrest_amount',10,2)->default(0);
            $table->integer('recommender_id')->nullable();
            $table->integer('status')->default(0);

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
        Schema::dropIfExists('loans');
    }
}
