<?php

/**
 * Enter description here...
 *
 */

Class Dropio_Manager {

  var $manager_token;
  var $dropio_api;

  /**
   * Enter description here...
   *
   * @param string $manager_token
   */
  
  function __construct ( $manager_token = null ) {
    $this->manager_token = $manager_token;
    $this->dropio_api = new Dropio_Api();
  }

  /**
   * Enter description here...
   *
   * @param string $manager_token
   * @return Dropio_Manager
   */
  
  static function factory ( $manager_token = null ) {
    return new Dropio_Manager($manager_token);
  }

  /**
   * Enter description here...
   *
   * @return Array
   */
  
  function getDrops ( $page = 1) {

    $result = $this->dropio_api->request('GET', 'accounts/drops',
    Array('manager_api_token'=>$this->manager_token, 'page'=>$page)
    );

    $out = Array();

    foreach ($result as $drop_array) {
      $drop = new Dropio_Drop();
      $drop->loadFromArray( $drop_array );
      $out[] = $drop;
    }

    return $out;

  }

  /**
   * Enter description here...
   *
   * @return Array
   */
  
  function getStats () {

    $result = $this->dropio_api->request('GET', 'accounts/drops',
    Array('manager_api_token'=>$this->manager_token)
    );

    return $result;

  }

}