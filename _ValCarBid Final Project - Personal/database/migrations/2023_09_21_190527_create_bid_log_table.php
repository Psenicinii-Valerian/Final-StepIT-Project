<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidLogTable extends Migration
{
    public function up()
    {
        Schema::create('bid_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->unsignedBigInteger('bidder_id');
            $table->unsignedBigInteger('listing_id');
            $table->integer('bid_price');
            $table->timestamps();
            
            $table->foreign('bidder_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bid_log');
    }
}
