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

  public function __construct($key=null,$secret=null) {
    if(is_null($key))
        throw new Dropio_Api_Exception('You must set an API key.');
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
   * @param boolean $b (default true)
   * @return mixed
   */
  public function setIsSecure($b = true)
  {
    $this->_use_https = $b;
    return $this;
  }

  protected function _signIfNeeded($params = null, $method = "POST")
  {
    if($this->_api_secret !== NULL)
    {
        $params = $this->_addRequiredParams($params);
        $params = $this->signRequest($params, $method);
    }
	
    return $params;
  }

  protected function _addRequiredParams($params = null)
  {
	  date_default_timezone_set('America/New_York');
      $params['timestamp'] = strtotime('now + 15 minutes');
      return $params;
  }

  public function signRequest($params = null, $method = "POST")
  {
    $str='';
    $this->ksortTree($params);
	
	#for GET and DELETE calls, all values are interpreted as strings, so convert them
	#before we JSON encode them. 
	if($method == "GET" || $method == "DELETE"){ 
   		foreach($params as $k=>$v){
	        $params[$k]=(string)$v;
		}
	}
	//print "\r\n Pingback url is: " . $params["pingback_url"];	
	$str = json_encode($params);
	//The ruby to_json does not add backslashes to slashes
	$str = stripslashes($str);
	#Debugging output
	//print("\r\nstring to sign was: " . $str . $this->_api_secret .  "\r\n\r\n");
	
	#add the signature to the params
    $params['signature'] = sha1($str . $this->_api_secret);
	
    return $params;
  }
  
	private function ksortTree( &$array )
	{
		if (!is_array($array)) {
			return false;
		}

		ksort($array);
		foreach ($array as $k=>$v) {
			$this->ksortTree($array[$k]);
		}
		return true;
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

    # Sign this api request if needed
    $params = $this->_signIfNeeded($params, $method);
	$ch = curl_init();

    # Setting the user agent, useful for debugging and allowing us to check which version
    curl_setopt($ch, CURLOPT_USERAGENT, 'Drop.io PHP client v' . self::CLIENT_VER);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
	curl_setopt($ch, CURLOPT_VERBOSE, true); // Display communication with server
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
	#curl_setopt($ch, CURLOPT_PROXY, "localhost:8888");
	
    switch($method)
    {
      case 'POST':

        curl_setopt($ch, CURLOPT_POST, 1);

        # For some reason, this needs to be a string instead of an array.
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
		#print(json_encode($params));
        break;
        case 'DELETE':
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
          break;
        case 'PUT':
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
          break;
        case 'GET':
          $url .= '?' . http_build_query($params);
          break;
        case 'UPLOAD':
          $params['file'] = '@' . $params['file'];

          $url = self::UPLOAD_URL;

          curl_setopt ($ch, CURLOPT_POST, 1);
          curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode($params));
          break;
      }

        
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
	#print("Raw curl response: " . $data);
    throw new Dropio_Api_Exception('Received error code from web server:' . $http_response_code . ' result: ' . $result,$http_response_code);
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

  /**
	*	Request conversion. 
	*	Because a conversion can act on many input and output assets, 
	*	it is not part of the asset class
	*/
	
  public function convert($asset_type, $inputs, $outputs, $using = null, $pingback_url = null){
	//There can be multiple inputs and multiple outputs, so we should
	//ensure that inputs and outputs are both an array of arrays.
	$inputs = is_array($inputs[0]) ? $inputs : array($inputs);
	$outputs = is_array($outputs[0]) ? $outputs : array($outputs); 
	$params = array(
		'inputs' => $inputs,
		'outputs' => $outputs,
		'job_type' => $asset_type
	);
	if(!empty($using)) { $params['using'] = $using; }
	if(!empty($pingback_url)) { $params['pingback_url'] = $pingback_url; }
	return $this->request('POST','jobs', $params);
  }

  public function getApiKey() { return $this->_api_key; }
  public function getApiSecret() { return $this->_api_secret; }
}
