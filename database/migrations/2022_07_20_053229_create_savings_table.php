<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('savings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->string('saving_type')->nullable();
            $table->double('saving_amount')->default(0);

            $table->string('issue_date_eng')->nullable();
            $table->string('issue_date_nep')->nullable();

            $table->integer('user_id')->nullable();
            $table->float('intrest_rate')->default(0);
            $table->double('intrest_amount')->default(0);

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
        Schema::dropIfExists('savings');
    }
}
