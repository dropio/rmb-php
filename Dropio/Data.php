<?php

/**
 * Enter description here...
 *
 */
Class Dropio_Data {

  var $loaded = false;
  var $values = Array();

  var $changed = Array();

  var $primary_key     = 'name';
  var $dropio_api;

  /**
   * Enter description here...
   *
   * @param string $uid
   */
  
  function __construct ( $uid = null ) {
    $this->values[$this->primary_key] = $uid;
    $this->dropio_api = new Dropio_Api();
  }

  /**
   * Enter description here...
   *
   * @param array $array
   * @return Dropio_Data
   */
  
  function loadFromArray( $array ) {
    //$this->values = Array();

    foreach ($array as $var=>$value) {
      $this->$var = $value;
    }

    $this->loaded = true;
    $this->changed = Array();

    //$this->values[$this->primary_key] = $value[$this->primary_key];

    return $this;
  }

  /**
   * Enter description here...
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
   * Enter description here...
   *
   * @param string $var
   * @return mixed
   */
  
  function __get( $var ) {

    if ($var == $this->primary_key && isset ( $this->values[$this->primary_key])) {
      return $this->values[$this->primary_key];
    }

    if (!$this->loaded)
    $this->load();

    return array_key_exists($var,$this->values)?$this->values[$var]:null;
  }

  /**
   * Enter description here...
   *
   * @param string $var
   * @param mixed $value
   * @return Dropio_Data
   */
  
  function __set( $var, $value ) {
    return $this->set($var, $value);
  }
  
}