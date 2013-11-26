<?php

use Illuminate\Database\Migrations\Migration;

class CreateUsers extends Migration {
	public function up(){
		Schema::create('users', function($table){
			$table->increments('id');
      $table->string('email')->unique();
      $table->string('password_digest');
      $table->integer('group_id')->unsigned();
			$table->timestamps();
		});
	}

	public function down(){
		Schema::drop('users');
	}
}
