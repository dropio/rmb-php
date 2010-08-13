<?php

Class Dropio_Exception extends Exception {};
Class Dropio_Api_Exception extends Dropio_Exception {};

Class Dropio_Api {

  const RESPONSE_FORMAT  = 'json';
  const API_VERSION      = '3.0';
  const API_URL          = 'api.drop.io';
  const CLIENT_VER       = '1.0';
  const UPLOAD_URL       = 'http://assets.drop.io/upload';

  private $_api_key      = null;
  private $_api_secret   = null;
  private $_use_https    = false;

  /**
   *
   * @var mixed  An associative array of values loaded from the API. Also used
   *             when sending values back to the server.
   */
  protected $_values       = null;

  public function __construct($key,$secret=null) {
    $this->_api_key    = $key;
    $this->_api_secret = $secret;
  }

  /**
  *   Set the API_KEY
   *
   * @param string The api key
  */
  private function setApiKey($v)
  {
    $this->_api_key = $v;
    return $this;
  }

  /**
  *   Set the API_SECRET
   *
   * @param string the secret associated with an api key
  */
  private function setApiSecret($v)
  {
    $this->_api_secret = $v;
    return $this;
  }

  /**
   *
   * @return mixed  An associative array of values loaded from the server
   */
  public function getValues()
  {
    return $this->_values;
  }

  /**
   *
   * @param array $values
   * @return <type>
   */
  public function setValues($values)
  {
    $this->_values = $values;
    return $this;
  }

  /**
   * Set whether the API call is secure (HTTPS) or insecure (HTTP)
   *
   * @param bolean $b (default true)
   * @return mixed
   */
  public function setIsSecure($b = true)
  {
    $this->_use_https = $b;
    return $this;
  }

  protected function _signIfNeeded($params = null)
  {
    if($this->_api_secret !== NULL)
    {
        $params = $this->_addRequiredParams($params);
        $params = $this->_signRequest($params);
    }

    return $params;
  }

  protected function _addRequiredParams($params = null)
  {
      $params['timestamp'] = strtotime('now + 15 minutes');
      return $params;
  }

  protected function _signRequest($params = null)
  {
    $str='';
    ksort($params);

    # Weird, if token is present but empty, remove it. Move this logic to
    # Drop object
    if(empty($params['token']))
      unset($params['token']);

    foreach($params as $k=>$v)
        $str .= "$k=$v";

    $params['signature'] = sha1($str . $this->_api_secret);

    return $params;
  }

  /**
   * Build a use that is either secure (HTTPS) or plain (HTTP)
   *
   * @return string   A complete URL that is either HTTP or HTTPS
   */
  private function getApiUrl()
  {
    return ($this->_use_https) ? 'https://'.self::API_URL : 'http://'.self::API_URL;
  }

  /**
   *
   * @param <type> $method
   * @param <type> $path
   * @param <type> $params
   * @return <type>
   */
  public function request($method, $path, $params=null)
  {
    $params['version'] = self::API_VERSION;
    $params['format']  = self::RESPONSE_FORMAT;
    $params['api_key'] = $this->_api_key;

    $url =  $this->getApiUrl() . '/' . $path;

    # Sign it, damn you!!
    $params = $this->_signIfNeeded($params);

    $ch = curl_init();

    # Setting the user agent, useful for debugging and allowing us to check which version
    curl_setopt($ch, CURLOPT_USERAGENT, 'Drop.io PHP client v' . self::CLIENT_VER);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);

    switch($method)
    {
      case 'POST':

        curl_setopt($ch, CURLOPT_POST, 1);

        # For some reason, this needs to be a string instead of an array.
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

    if ( ( $result = curl_exec($ch) ) === false )
      throw new Dropio_Api_Exception ('Curl Error:' . curl_error($ch));

    $http_response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (in_array($http_response_code, Array(200,400,403,404)) && is_array( $data = @json_decode( $result, true)))
    {
      if (isset($data['response']['result']) && $data['response']['result'] == 'Failure')
      {
        throw new Dropio_Api_Exception ($data['response']['message']);
      }
      return $data;
    }

    throw new Dropio_Api_Exception('Received error code from web server:' . $http_response_code,$http_response_code);
  }

  /**
   * Get a list of all drops associated with this key
   *
   * @return array  An array of all the drops associated with this key
   */
  public function getDrops()
  {
    return $this->request('GET','accounts/drops',array());
  }

  /**
   * Get some stats associated with this key
   *
   * @return <type>
   */
  public function getStats()
  {
    return $this->request('GET','accounts/stats',array());
  }

  /**
  * Get an instance which enables fluent interface / chaining x->a()->b()->c();
  */
  public static function getInstance($api_key,$api_secret=null)
  {
    return new Dropio_Api($api_key,$api_secret);
  }

  public function getApiKey() { return $this->_api_key; }
  public function getApiSecret() { return $this->_api_secret; }
}
