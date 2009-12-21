<?php

/**
 * Dropio_Drop_Subscription represents a subscription. 
 *
 * 
 * Example to add a pingback subscription to a drop:
 * 
 *  $drop = Dropio_Drop::instance()->save();
 *
 *  $subscription = Dropio_Drop_Subscription::instance($drop)
 *    ->set('type', 'pingback')
 *    ->set('url', 'http://example.org/' . md5($a))
 *    ->save();
 *      
 */

Class Dropio_Drop_Subscription extends Dropio_Data {

  var $drop = null;
  var $primary_key = 'id';

  /**
	 * The constructor takes in the drop which the subscriptiong is being added to
	 * and the optional subscription id.
	 *
	 * @param Dropio_Drop $drop
	 * @param integer $subscription_id
	 */

  function __construct ( Dropio_Drop &$drop, $subscription_id = null ) {

    $this->drop = $drop;
    $this->values[$this->primary_key] = $subscription_id;

  }

  /**
   * Returns a new or previously created subscription.
   *
   * @param Dropio_Drop $drop
   * @param integer $subscription_id
   * @return Dropio_Drop_Subscription
   */
  static function instance ( Dropio_Drop &$drop, $subscription_id = null ) {

    $subscription = new Dropio_Drop_Subscription($drop, $subscription_id);
    return $subscription;

  }

  /**
   * Create an object from an array.
   *
   * @param unknown_type $array
   * @return unknown
   */
  
  function loadFromArray( $array ) {

    if ($array['type'] == 'pingback') {
      $array['url'] = $array['username'];
    }
    
    return parent::loadFromArray($array);
  }


  /**
	 * Load a subscription.
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
	 * Delete a subscription, returns an array directly from the API.
	 *
	 * @return Array
	 */

  function delete() {

    $result = $this->drop->dropio_api->request('DELETE', 'drops/' . $this->drop->name . '/subscriptions/' . $this->values[$this->primary_key],
    Array(
    'token'=>$this->drop->token()
    ));

    return $result;

  }

  /**
	 * Writes the subscription to the API.
	 *
	 * @return Dropio_Drop_Subscription
	 */

  function save() {

    $this->values['token'] = $this->drop->token();

    $result = $this->drop->dropio_api->request(
    'POST',
    'drops/' . $this->drop->name . '/subscriptions',
    $this->values
    );

    return Dropio_Drop_Subscription::instance($this->drop)->loadFromArray($result);

  }

}