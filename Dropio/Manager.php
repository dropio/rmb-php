<?php

/**
 * Dropio_Manager is used to interface with the Drop.io API's manager features.
 * 
 * Given the api_key for a manager account, usage and drop information can be 
 * retrieved.
 *
 */

Class Dropio_Manager {

  var $manager_token;
  var $dropio_api;

  /**
   * $manager_token is not needed for premiun api_key users.
   *
   * @param string $manager_token
   */
  
  function __construct ( $manager_token = null ) {
    $this->manager_token = $manager_token;
    $this->dropio_api = new Dropio_Api();
  }

  /**
   * Same as the constructor, used to chain object methods.
   *
   * @param string $manager_token
   * @return Dropio_Manager
   */
  
  static function instance ( $manager_token = null ) {
    return new Dropio_Manager($manager_token);
  }

  /**
   * Gets drop objects from a manager acocunt.  Each page contains up to 30 
   * drops.
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
   * Retrieves status on manager account.
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