<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ap_vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('bill_id')->unique();
            $table->date('invoice_billing_date')->nullable();
            $table->date('bmo_received_date')->nullable();
            $table->string('rush')->nullable();
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
        Schema::dropIfExists('ap_vouchers');
    }
}
