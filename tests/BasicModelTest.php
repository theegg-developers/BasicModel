<?php
require_once __DIR__ . '/TestCase.php';

class BasicModelTest extends TestCase {
  
  public function testCreateNestedAssociation(){
    $this->createUserWithOrder();
    $user = User::first();
    $this->assertTrue($user->orders()->first()->name == 'Order 1');
  }

  public function testUpdateNestedAssociation(){
    $this->createUserWithOrder();
    $user = User::first();
    $data = array(
      'email' => 'doe@mail.com',
      'orders_attributes' => array('id'=>1, 'name'=> 'other_name')
      );
    $user->fill($data);
    $user->save();
    $this->assertEquals(User::first()->email, 'doe@mail.com');
    $this->assertEquals(Order::first()->name,'other name');
  }

  // // public function testBasicExample(){
  // //   $crawler = $this->client->request('GET', '/');
  // //   User::create(array('email'=> 'asdfasdfasdfasd'));
  // //   $user = User::first();
  // //   echo $user;
  // //   $this->assertTrue($this->client->getResponse()->isOk());
  // // }
  
  private function createUserWithOrder(){
    $user = User::create(array(
      'email'=>'john@mail.com',
      'orders_attributes' => array(array('name'=>'Order 1'))
      )
    );
  }
}
