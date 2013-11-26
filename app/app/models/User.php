<?php
require_once __DIR__. '/../../../src/TheEgg/BasicModel/BasicModel.php';

class User extends \TheEgg\BasicModel\BasicModel{
  public $fillable = array('orders_attributes','email');
  static $relationsData = array(
    'orders' => array(self::HAS_MANY, 'Order')
  );
  static $accept_nested_attributes = array('orders');
}