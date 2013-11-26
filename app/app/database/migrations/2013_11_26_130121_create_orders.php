<?php

use Illuminate\Database\Migrations\Migration;

class CreateOrders extends Migration {
	public function up(){
		Schema::create('orders', function($table){
			$table->increments('id');
      $table->string('name');
      $table->integer('user_id')->unsigned();
			$table->timestamps();
		});
	}

	public function down(){
		Schema::drop('orders');
	}
}
