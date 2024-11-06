<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ec_products', function (Blueprint $table) {
            $table->json('documents')->nullable(); // or any type you prefer
        });
    }
    
    public function down()
    {
        Schema::table('ec_products', function (Blueprint $table) {
            $table->dropColumn('documents');
        });
    }
    
};
