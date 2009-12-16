<?php

include 'Data.php';
include 'Set.php';
include 'Drop.php';
include 'Drop/Set.php';
include 'Manager.php';
include 'Asset.php';
include 'Drop/Subscription.php';
include 'Drop/Subscription/Set.php';
include 'Asset/Set.php';
include 'Asset/Comment.php';
include 'Asset/Comment/Set.php';

if (!extension_loaded('curl')) {
  throw new Dropio_Exception('This library requires the Curl extension.  Read more: http://php.net/manual/en/book.curl.php');
} 

Class Dropio_Exception extends Exception {};
Class Dropio_Api_Exception extends Dropio_Exception {};

/**
 * Dropio_Api is a client for the Dropio API and the basis for all the other 
 * helper classes.
 * 
 * This can be used to access all the functionality of the API.  
 * 
 * If an error is returned from the API, a Dropio_Api_Exception is thrown.
 * 
 * Example to get details on a drop.
 * 
 * try {
 *  $api = new Dropio_Api(API_KEY);
 *  $response = $api->request('GET', '/drops/php_api_lib');
 *  print_r($response);
 * } catch (Dropio_Api_Exception $e) {
 *  die("Error:" . $e->getMessage());
 * }
 *
 */

Class Dropio_Api {

  const RESPONSE_FORMAT  = 'json';
  const API_VERSION      = '2.0';

  protected $api_key     = null;
  static $global_api_key = null;

  static $use_https      = false;
	static $api_url        = null;
  const API_HTTP_URL     = 'http://api.drop.io';
  const API_HTTPS_URL    = 'http://api.drop.io';
	const CLIENT_VER       = '1.0';
  const UPLOAD_URL       = 'http://assets.drop.io/upload';

  /**
	 * instantiates a new Dropio_Api object.  The api_key is optional, if not set
	 * it uses the global api_key set by: Dropio_Api::setKey(API_KEY);
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
	 * Instance method to allow simple chaining.
	 * 
	 * Example:
	 * 
	 * $response = Dropio_Api::instance()->request('GET', '/drops/php_api_lib');
	 *
	 * @param string $api_key
	 * @return Dropio_Api
	 */

  static function instance ( $api_key = null ) {
    if (empty($api_key)) {
      $api_key = self::$global_api_key;
    }

    return new Dropio_Api( $api_key );
  }

  /**
	 * Sets the global api_key.
	 *
	 * @param string $api_key
	 */

  static function setKey( $api_key ) {
    self::$global_api_key = $api_key;
  }


  /**
	 * Sets the global api_key.
	 *
	 * @param string $api_key
	 */
	
	static function setApiUrl( $url ) {
		self::$api_url = $url;
	}
	
  /**
	 * Executes a request to Drop.io's API servers.  
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

		$api_url  = empty(self::$api_url)?(self::$use_https?self::API_HTTPS_URL:self::API_HTTP_URL):self::$api_url;
    $url      =  $api_url . '/' . $path;

    $ch = curl_init();

		/**
		*  Setting the user agent, useful for debugging and allowing us to check which version
		**/
		
    curl_setopt($ch, CURLOPT_USERAGENT, 'Drop.io PHP client v' . self::CLIENT_VER);

    switch($method){
      case 'POST':
        curl_setopt($ch, CURLOPT_POST, 1);

        //For some reason, this needs to be a string instead of an array.
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

    //echo $url;print_r($params); echo "\n";
    
    
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

      if (
      isset($data['response']['result'])
      &&
      $data['response']['result'] == 'Failure'
      ) {
        throw new Dropio_Api_Exception ($data['response']['message']);
      }

      return $data;
    }

    throw new Dropio_Api_Exception(
    'Received error code from web server:' . $http_response_code,
    $http_response_code
    );

  }

}