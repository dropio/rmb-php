<?php

/**
 * Enter description here...
 *
 */

Class Dropio_Drop_Subscription extends Dropio_Data {

  var $drop = null;
  var $primary_key = 'id';

  /**
	 * Enter description here...
	 *
	 * @param Dropio_Drop $drop
	 * @param integer $subscription_id
	 */

  function __construct ( Dropio_Drop &$drop, $subscription_id = null ) {

    $this->drop = $drop;
    $this->values[$this->primary_key] = $subscription_id;

  }
  
  static function factory ( Dropio_Drop &$drop, $subscription_id = null ) {
    
    $subscription = new Dropio_Drop_Subscription($drop, $subscription_id);
    return $subscription;
    
  }
  
  /**
	 * Enter description here...
	 *
	 * @return Dropio_Drop_Subscription
	 */

  function load() {

    if (!$this->values[$this->primary_key])
    return $this;

    $this->values['token'] = $this->drop->token();

    $result = $this->drop->dropio_api->request('GET', 'drops/' . $this->drop->name . '/subscriptions/' . $this->values[$this->primary_key], $this->values);

    return $this->loadFromArray( $result );

  }

  /**
	 * Enter description here...
	 *
	 * @return Dropio_Drop_Subscription
	 */

  function delete() {

    $result = $this->drop->dropio_api->request('DELETE', 'drops/' . $this->drop->name . '/subscriptions/' . $this->values[$this->primary_key],
    Array(
    'token'=>$this->drop->token()
    ));

    return $result;

  }

  /**
	 * Enter description here...
	 *
	 * @return Dropio_Drop_Subscription
	 */

  function save() {

    $this->values['token'] = $this->drop->token();

    $result = $this->drop->dropio_api->request('POST', 'drops/' . $this->drop->name . '/subscriptions', $this->values);

    //Result will be an array of subscriptions;

    $out = Array();

    foreach ($result as $sub_array) {
      $subscription = new Dropio_Drop_Subscription( $this->drop );
      $out[] = $subscription->loadFromArray($sub_array);
    }

    return $out;

  }

}