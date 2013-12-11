<?php 

namespace TheEgg\BasicModel;

use Illuminate\Support\Facades\DB;

abstract class BasicModel extends \LaravelBook\Ardent\Ardent{

  static public $relationsData=array();
  static $accept_nested_attributes =array();

  public function save(array $rules = array(),
                        array $customMessages = array(),
                        array $options = array(),
                        Closure $beforeSave = null,
                        Closure $afterSave = null) {

    $nested_attributes = $this->extractNestedAttributes();

    // Begin Transaction
    try{DB::connection()->getPdo()->beginTransaction();}catch(\PDOException $e){}
    
    try {
      if(! parent::save())
        throw new \Exception('invalid parent');

      $this->saveNestedAttributes($nested_attributes);

      // Commit transaction
      try{DB::connection()->getPdo()->commit();}catch(\PDOException $e){}
    }
    catch(\Exception $e) {
      //echo $this->errors();
      //throw $e;
      //ddd('a');
      
      // Rollback if save failed
      try{DB::connection()->getPdo()->rollBack();}catch(\PDOException $e){}
      return false;
    }

    return true;
  }

  function saveNestedAttributes($nested_attributes){
    foreach($nested_attributes as $relation=>$attributes)
      foreach($attributes as $attribute){
        $this->saveChild($relation, $attribute);
      }
  }

  function saveChild($relation, $attributes){
    if(isset($attributes['id'])){
      $child = $this->{$relation}()->find($attributes['id']);
      if(! $child)
        return false;
      unset($attributes['id']);
      if(isset($attributes['_destroy']) && $attributes['_destroy'])
        return $child->destroy($child->id);
      $child->fill($attributes);
      if( ! $child->save()){
        $this->validationErrors = $child->errors();
        throw new \Exception('invalid child');
      }
    }
    else{
      $child = $this->{$relation}()->create($attributes);
      if(! $child->save()){
        $this->validationErrors = $child->errors();
        throw new \Exception('invalid child'); 
      }
    }
  }

  protected function extractNestedAttributes(){
    $nested_attributes = static::$accept_nested_attributes;
    $result = array();
    foreach($nested_attributes as $attributes) {
      foreach($attributes as $key=>$field){
        if($this->{$field} !== NULL){
          $relation = camel_case(preg_replace('/_attributes/', '', $field));
          $result[$relation] = $this->{$field};
          unset($this->{$field});
        }
      }
    }
    return $result;
  }

//Next methods should go in traits
  function getFillableSchemaAttributes(){
    $results = $this->getSchemaAttributes();
    foreach($results as $key=>$r)
      if(!in_array($r['name'], $this->getFillable()))
        unset($results[$key]);
    return $results;
  }

  function getSchemaAttributes(){
    $table = $this->getTable();
    $schema = DB::getDoctrineSchemaManager($table);
    $columns = $schema->listTableColumns($table);
    $result = array();
    foreach($columns as $column){
      $result[] = array(
        "name" => $column->getName(),
        "type" =>  $column->getType()->getName(),
        "length" => $column->getLength(),
        "default" => $column->getDefault(),
        "form_builder_input" => $this->getInputHelper($column->getType()->getName())
      );
    }
    return $result;
  }

  function to_s(){
    return get_class($this) . ": " .$this->id;
  }

  function getAttributeRelation($attribute){
    $relations_data = static::$relationsData;
    $relation = null;
    if(substr($attribute, -3) == '_id' && isset($relations_data[substr($attribute,0,-3)])){
      $relation = $relations_data[substr($attribute,0,-3)];
      $relation['method'] = substr($attribute,0,-3);
    }
    else foreach($relations_data as $key=>$r)
      if(isset($r[2]) && $r[2]==$attribute ){
        $relation = $r[2];
        $relation['method'] = $key;
      }
    return $relation;
  }

  private function getInputHelper($type){
    $conversions = array(
      'integer' => 'number',
      'decimal' => 'number',
      'float'   => 'number',
      'date'    => 'date'
    );
    if(array_key_exists($type, $conversions))
      return $conversions[$type] . '_input';
    return 'text_input';
  }

//Validate, improve Ardent (@todo: issue pull request)
//Use mutated attributes too
  // public function validate(array $rules = array(), array $customMessages = array()) {
  //     if ($this->fireModelEvent('validating') === false) {
  //       if ($this->throwOnValidation) {
  //           throw new InvalidModelException($this);
  //       } else {
  //           return false;
  //       }
  //     }
  //     // check for overrides, then remove any empty rules
  //     $rules = (empty($rules))? static::$rules : $rules;
  //     foreach ($rules as $field => $rls) {
  //       if ($rls == '') {
  //           unset($rules[$field]);
  //       }
  //     }

  //     if (empty($rules)) {
  //       $success = true;
  //     } else {
  //       $customMessages = (empty($customMessages))? static::$customMessages : $customMessages;
  //       if ($this->forceEntityHydrationFromInput || (empty($this->attributes) && $this->autoHydrateEntityFromInput)) {
  //         $this->fill(Input::all());
  //       }
  //       $data = $this->getAttributesIncludingMutated();
  //       $validator = self::makeValidator($data, $rules, $customMessages);
  //       $success   = $validator->passes();
  //       if ($success) {
  //         // if the model is valid, unset old errors
  //         if ($this->validationErrors->count() > 0) {
  //                 $this->validationErrors = new MessageBag;
  //         }
  //       } else {
  //       // otherwise set the new ones
  //       $this->validationErrors = $validator->messages();
  //       // stash the input to the current session
  //       if (!self::$externalValidator && Input::hasSessionStore()) {
  //         Input::flash();
  //       }
  //     }
  //   }
  //   $this->fireModelEvent('validated', false);
  //   if (!$success && $this->throwOnValidation) {
  //           throw new InvalidModelException($this);
  //   }
  //   return $success;
  // }
  // //This too, Ardent pull request
  // private function getAttributesIncludingMutated(){
  //   $data = $this->getAttributes();
  //   foreach($this->getMutatedAttributes() as $key)
  //     $data[$key] = $this->mutateAttribute($key, null);
  //   return $data;
  // }

}