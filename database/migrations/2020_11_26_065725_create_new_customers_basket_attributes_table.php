<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewCustomersBasketAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_customers_basket_attributes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('customers_id')->nullable();
            $table->integer('products_id')->nullable();
            $table->integer('products_options_id')->nullable();
            $table->string('products_options_name')->nullable();
            $table->string('products_options_values')->nullable();
            $table->boolean('is_orders')->default(0);
            $table->string('session_id')->nullable();
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
        Schema::dropIfExists('new_customers_basket_attributes');
    }
}
