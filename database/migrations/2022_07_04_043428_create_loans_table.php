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
            $table->double('loan_amount')->default(0);
            $table->string('loan_duration')->default(0);
            $table->string('loan_purpose')->nullable();
            $table->integer('installation_type')->default(0);
            $table->string('recommend_to')->nullable();
            $table->string('issue_date')->nullable();
            $table->string('due_date')->nullable();
           
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
