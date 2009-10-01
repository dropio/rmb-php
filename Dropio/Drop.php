<?php

Class Dropio_Drop_Exception extends Dropio_Exception{};

/**
 * Dropio_Drop is used to access all functionality related to a Drop.  Most 
 * methods are chainable, allowing for the the creation of a drop to happen
 * inline.  
 * 
 * For example, to create a drop and to access it.
 * 
 * $drop = Dropio_Drop::factory('dropname')->save();
 * 
 * To load a pre-existing drop and load:
 * 
 * $drop = Dropio_Drop::load('dropname');
 * 
 */

Class Dropio_Drop extends Dropio_Data {

  var $dropio_api = null;
  var $token = null;

  /**
   * Standard constructor.  $drop_name can be set to either later load or 
   * create a new drop.
   *
   * @param string $name
   * @param string $token
   */
  
  function __construct ( $drop_name = null, $token = null ) {
    $this->dropio_api = new Dropio_Api();
    $this->values[$this->primary_key] = $drop_name;
    $this->token = $token;
  }

  /**
   * Factory method that allows for chaining such as:
   * $asset = Drop_Dropio::factory()->save()->addFile('/tmp/file');
   * 
   * @param string $name
   * @param string $token
   * @return Dropio_Drop
   */
  
  static function factory ( $name = null, $token = null ) {
    return new Dropio_Drop ( $name, $token );
  }
  
  
  /**
   * Loads a drop.  This makes an immediate API call for all drop details.
   *
   * @param string $name
   * @param string $token
   * @return Dropio_Drop
   */
  
  static function load ( $name, $token = null ) {
    $drop = new Dropio_Drop ( $name, $token );
    return $drop->_load($name, $token);
  }

  /**
   * Enter description here...
   *
   * @param string $name
   * @param string $token
   * @return Dropio_Drop
   */
  
  function _load ( $name = null, $token = null ) {

    if (!strlen($name) && isset($this->values[$this->primary_key])) {
      $name = $this->values[$this->primary_key] ;
    }

    if (strlen($token)) {
      $this->token = $token;
    }

    if (!strlen($name)) {
      throw new Dropio_Drop_Exception( 'Name must be set in order to load' );
    }

    $result = $this->dropio_api->request('GET', 'drops/' . $name,
    Array('token'=>$this->token())
    );

    return $this->loadFromArray($result);

  }


  function token () {

    switch (true) {
      case strlen($this->token):
        return $this->token;
      case isset($this->values['admin_token']) && strlen($this->values['admin_token']):
        return $this->values['admin_token'];
      case isset($this->values['guest_token']) && strlen($this->values['guest_token']):
        return $this->values['guest_token'];

      default:
        //throw new Dropio_Exception('Unable to find token for this drop');
    }

  }


  /**
   * Enter description here...
   *
   * @return Dropio_Drop
   */
  
  function save () {

    if (!$this->loaded) {
      
      //We'll create a new one.
      $result = $this->dropio_api->request('POST', 'drops', $this->values);

      foreach ($result as $var=>$value) {
        $this->$var = $value;
      }

      $this->loaded = true;

    } else {
      //Updating;

      $updates = Array();

      foreach ($this->changed as $var) {
        if (array_key_exists($var, $this->values)) {
          if (is_bool($this->values[$var]))
          $updates[$var] = $this->values[$var]?'true':'false';
          else
          $updates[$var] = $this->values[$var];
        }

      }

      $updates['token'] = $this->token();

      $result = $this->dropio_api->request('PUT', 'drops/' . $this->name, $updates);

      return $this->loadFromArray($result);
    }

    return $this;

  }

  function getAsset ( $asset_name ) {

    $asset_array = $this->dropio_api->request('GET', 'drops/' . $this->name . '/assets/' . $asset_name,
    Array('token'=>$this->token())
    );

    $asset = new Dropio_Asset();
    $asset->drop = $this;

    return $asset->loadFromArray($asset_array);

  }

  /**
   * Enter description here...
   *
   * @param integer $page
   * @return Dropio_Drop
   */

  function assets ( $page = 1, $order = 'oldest') {

    if (!in_array($order, Array('oldest', 'latest'))){
      throw new Dropio_Drop_Exception('Invalid value for order, must be either: oldest or latest');
    }
    
    $result = $this->dropio_api->request('GET', 'drops/' . $this->name . '/assets',
    Array(
      'page'=>$page, 
      'token'=>$this->token(),
      'order'=>$order
    )
    );

    $out = Array();
    foreach ( $result as $asset_array) {
      $asset = new Dropio_Asset();
      $asset->drop = $this;

      $out[ $asset_array['name'] ] = $asset->loadFromArray($asset_array);
    }

    return $out;

  }

  /**
   * Enter description here...
   *
   * @param string $contents
   * @param string $title
   * @return Dropio_Asset
   */
  
  function addNote ( $contents, $title = null ) {

    $response = $this->dropio_api->request('POST','drops/' . $this->name . '/assets',
    Array(
    'drop_name'=> $this->name,
    'contents' => $contents,
    'title'    => $title,
    'token'    => $this->token()
    )
    );

    $asset       = new Dropio_Asset();
    $asset->drop = $this;
    
    return $asset->loadFromArray($response);

  }

  /**
   * Enter description here...
   *
   * @param string $url
   * @param string $title
   * @param string $description
   * @return Dropio_Asset
   */

  function addLink ( $url, $title = null, $description = null ) {

    $response = $this->dropio_api->request('POST','drops/' . $this->name . '/assets',
    Array(
    'drop_name'		=> $this->name,
    'title'    		=> $title,
    'url'    			=> $url,
    'description' => $description,
    'token'				=> $this->token()
    )
    );

    $asset       = new Dropio_Asset();
    $asset->drop = $this;
    return $asset->loadFromArray($response);

  }

  /**
   * Enter description here...
   *
   * @param string $file_url
   * @return Dropio_Asset
   */
  
  function addFileUrl ( $file_url ) {
    
    $response = $this->dropio_api->request('POST','drops/' . $this->name . '/assets',
    Array(
    'drop_name'		=> $this->name,
    'file_url'    => $file_url,
    'token'				=> $this->token()
    )
    );

    $asset       = new Dropio_Asset();
    $asset->drop = $this;
    
    return $asset->loadFromArray($response);

  }


  /**
   * Enter description here...
   *
   * @param string $file
   * @return Dropio_Asset
   */
  
  function addFile ( $file ) {

    if (!$this->loaded) {
      $this->load();
    }

    if (!file_exists($file)) {
      throw new Dropio_Drop_Exception('File does not exist: ' . $file);
    }
    
    $response = $this->dropio_api->request('UPLOAD',null,
    Array(
    'drop_name' => $this->name,
    'file'      => $file,
    'token'     => $this->token()
    )
    );

    $asset = new Dropio_Asset();
    $asset->drop = $this;
    return $asset->loadFromArray($response);

  }

  /**
   * Enter description here...
   *
   * @return Dropio_Drop
   */
  
  function delete() {

    $result = $this->dropio_api->request('DELETE','drops/' . $this->name,
    Array('token'=>$this->token())
    );

    return $result;

  }

  /**
   * Enter description here...
   *
   * @return Dropio_Drop
   */

  function emptyAssets() {

    $result = $this->dropio_api->request('PUT','drops/' . $this->name . '/empty',
    Array('token'=>$this->token())
    );

    return $this;
    
  }

 
  /**
   * Enter description here...
   *
   * @param string $nick
   * @return array
   */
  
  function promoteNick ( $nick ) {

    return $this->dropio_api->request('POST','drops/' . $this->name . '/promote',
    Array(
    'token'				=> $this->token(),
    'nick'				=> $nick
    )
    );

  }

  /**
   * Enter description here...
   *
   * @param integer $expires_in
   * @return string
   */

  function getRedirectUrl ( $expires_in = 900 ) {

    $expires = time() + $expires_in;

    $signature = sha1($expires.'+'.$this->token().'+'.$this->name);

    return "http://drop.io/{$this->name}/from_api/?version=2.0&signature=${signature}&expires=${expires}";

  }
  
  /**
   * Returns a HTML for a file uploader.
   *
   * @return unknown
   */
 function getEmbedCode () {
    
    $result = $this->dropio_api->request('GET','drops/' . $this->name . '/upload_code',
    Array('token'=>$this->token())
    );

    return $result['upload_code'];
    
  }
  


}


