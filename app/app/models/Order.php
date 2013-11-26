<?php

class Order extends \TheEgg\BasicModel\BasicModel{
  public $fillable = array('name','user_id');
  static $relationsData = array(
    'user' => array(self::BELONGS_TO, 'User')
  );
}