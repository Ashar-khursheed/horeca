<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEliteshipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eliteshipment', function (Blueprint $table) {
            $table->id();
            $table->string('shipper_name')->nullable();
            $table->string('shipper_address')->nullable();
            $table->string('shipper_area')->nullable();
            $table->string('shipper_city')->nullable();
            $table->string('shipper_telephone')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_address')->nullable();
            $table->string('receiver_address2')->nullable();
            $table->string('receiver_area')->nullable();
            $table->string('receiver_city')->nullable();
            $table->string('receiver_telephone')->nullable();
            $table->string('receiver_mobile')->nullable();
            $table->string('receiver_email')->nullable();
            $table->string('shipping_reference')->nullable();
            $table->text('orders')->nullable();
            $table->string('item_type')->nullable();
            $table->text('item_description')->nullable();
            $table->decimal('item_value', 10, 2)->nullable();
            $table->string('dangerous_goods_type')->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->integer('no_of_pieces')->nullable();
            $table->string('service_type')->nullable();
            $table->decimal('cod_value', 10, 2)->nullable();
            $table->date('service_date')->nullable();
            $table->time('service_time')->nullable();
            $table->string('created_by')->nullable();
            $table->text('special')->nullable();
            $table->string('order_type')->nullable();
            $table->string('ship_region')->default('AE')->nullable();
            $table->string('AWB')->nullable()->unique();
            $table->string('status')->default('Pending')->nullable();
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
        Schema::dropIfExists('eliteshipment');
    }
}
