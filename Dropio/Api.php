<?php

Class Dropio_Exception extends Exception {};
Class Dropio_Api_Exception extends Dropio_Exception {};

/**
 * Enter description here...
 *
 */

Class Dropio_Api {

  const RESPONSE_FORMAT  = 'json';
  const API_VERSION      = '2.0';

  protected $api_key     = null;
  static $global_api_key = null;

  const API_URL    = 'http://api.drop.io';
  const UPLOAD_URL = 'http://assets.drop.io/upload';

  /**
	 * Enter description here...
	 *
	 * @param string $api_key
	 */

  function __construct ( $api_key = null ) {

    if (empty($api_key)) {
      $api_key = self::$global_api_key;
    }

    if (empty($api_key)) {
      throw new Dropio_Api_Exception('Api key is not set.');
    }
    $this->api_key = $api_key;

  }

  /**
	 * Enter description here...
	 *
	 * @param string $api_key
	 * @return Dropio_Api
	 */

  static function factory ( $api_key = null ) {
    if (empty($api_key)) {
      $api_key = self::$global_api_key;
    }

    return new Dropio_Api( $api_key );
  }

  /**
	 * Enter description here...
	 *
	 * @param string $api_key
	 */

  static function setKey( $api_key ) {
    self::$global_api_key = $api_key;
  }

  /**
	 * Enter description here...
	 *
	 * @param string $method
	 * @param string $path
	 * @param array $params
	 * @return mixed
	 */

  function request ( $method, $path, $params = Array() ) {

    $params['version'] = self::API_VERSION;
    $params['format']  = self::RESPONSE_FORMAT;

    $params['api_key'] = $this->api_key;

    $url =  self::API_URL . '/' . $path;

    $ch = curl_init();

    switch($method){
      case 'POST':
        curl_setopt($ch, CURLOPT_POST, 1);

        //For some reason, this needs to be a string instead of an array. No clue why.
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        break;
      case 'DELETE':
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        break;
      case 'PUT':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        break;
      case 'GET':
        $url .= '?' . http_build_query($params);
        break;
      case 'UPLOAD':
        $params['file'] = '@' . $params['file'];
        $url = self::UPLOAD_URL;

        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
        break;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if ( ( $result = curl_exec($ch) ) === false ) {
      throw new Dropio_Api_Exception ('Curl Error:' . curl_error($ch));
    }

    $http_response_code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (
    in_array($http_response_code, Array(200,400,403,404))
    &&
    is_array( $data = @json_decode( $result, true))
    ) {

      if (isset($data['response']['result']) && $data['response']['result'] == 'Failure') {
        throw new Dropio_Api_Exception ($data['response']['message']);
      }

      return $data;
    }

    throw new Dropio_Api_Exception( 'Received error code from web server:' . $http_response_code, $http_response_code);

  }

}