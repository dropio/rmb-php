<?php

/**
 * Abstract class that is extended to create helper data objects.
 *
 */

Abstract Class Dropio_Data {

  var $loaded = false;
  var $values = Array();

  var $changed = Array();

  var $primary_key = 'name';
  var $dropio_api;

  /**
   * Constructor with optional unique id. 
   *
   * @param string $uid
   */
  
  function __construct ( $uid = null ) {
    $this->values[$this->primary_key] = $uid;
    $this->dropio_api = new Dropio_Api();
  }

  /**
   * Set object properties from an array.
   *
   * @param array $array
   * @return Dropio_Data
   */
  
  function loadFromArray( $array ) {
    
    foreach ($array as $var=>$value) {
      $this->$var = $value;
    }

    $this->loaded = true;
    $this->changed = Array();

    return $this;
  }

  /**
   * Instead using the magic __SET() function, this can be used in order to 
   * facilitate chaining.  
   * 
   * $obj->set('title', 'My Title')->set('name', 'Name)->..
   *
   * @param string $var
   * @param mixed $val
   * @return Dropio_Data
   */
  
  function set ( $var, $value ) {
    $this->values[$var] = $value;
    $this->changed[] = $var;
    return $this;
  }

  /**
   * Magic method that returns the object's parameters.
   *
   * @param string $var
   * @return mixed
   */
  
  function __get( $var ) {

    if ($var == $this->primary_key && isset ( $this->values[$this->primary_key])) {
      return $this->values[$this->primary_key];
    }

    if (!$this->loaded) {
      $this->load();
    }

    return array_key_exists($var,$this->values)?$this->values[$var]:null;
  }

  /**
   * Magic __set method that is mapped to the set() method.
   *
   * @param string $var
   * @param mixed $value
   * @return Dropio_Data
   */
  
  function __set( $var, $value ) {
    return $this->set($var, $value);
  }
  
}