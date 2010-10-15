<?php

# Drop requires both API access and Asset
include_once(dirname(__FILE__) . '/Api.php');
include_once(dirname(__FILE__) . '/Asset.php');

Class Rmb_Drop_Exception extends Rmb_Exception{};

/**
 * Rmb_Drop is used to access all functionality related to a Drop.  Most 
 * methods are chainable, allowing for the the creation of a drop to happen
 * inline.  
 * 
 * For example, to create a drop and to access it.
 * 
 * $drop = Rmb_Drop::getInstance($API_KEY)->save();
 * 
 * To load a pre-existing drop and load:
 * 
 * $drop = Rmb_Drop::load('dropname');
 * 
 */

Class Rmb_Drop extends Rmb_Api {

  private $_origName = null;
  private $_token    = null;
  private $_assets   = array();  # Array of asset objects

  /**
   * @var boolean Has a drop already been loaded?
   */
  private $_is_loaded = false;


  /**
   * Load a drop by name (dropname)
   *
   * @param <type> $dropname
   * @return <type>
   */
  public function load($dropname)
  {
    $this->setValues($this->request('GET', "drops/$dropname", array()));
    $this->_origName = $dropname;
    $this->_is_loaded = true;
    return $this;
  }

  /*
   * Help function used to format data array when updating a drop
   */
  private function prepareUpdate()
  {
	$dropOptions = array();
	$name = $this->getName();
	$description = $this->getDescription();
	$max_size = $this->getMaxSize();
	$email_key = $this->getEmailKey();
	$chat_password = $this->getChatPassword();
	
	#include each option only if it is set.
	if(!empty($name)){ $dropOptions['name'] = $name; }
	if(!empty($description)){ $dropOptions['description'] = $description; }
	if(!empty($max_size)){ $dropOptions['max_size'] = $max_size; }
	if(!empty($email_key)){ $dropOptions['email_key'] = $email_key; }
	if(!empty($chat_password)){ $dropOptions['chat_password'] = $chat_password; }
	
	#return the dropOptions array to the call
	return $dropOptions;
  }

  /**
   * Save data back to a drop
   */
  public function save()
  {
    # If _is_loaded is false, the drop is new. Otherwise update
    if (!$this->_is_loaded) {
      $result = $this->request('POST', 'drops', $this->prepareUpdate());
      $this->_is_loaded = true;
    } else {
      $result = $this->request('PUT', 'drops/' . $this->_origName, $this->prepareUpdate());
    }

    $this->setValues($result);
    $this->_origName = $this->getName();
    return $this;
  }

  /**
   * Delete a drop and all its contents
   *
   * @link http://docs.rmb.io/Delete-a-Drop
   * @return mixed
   */
  public function delete($drop_name=null)
  {
    if($drop_name !== NULL) { 
      $this->setName($drop_name); 
    }
    $result = $this->request('DELETE','drops/' . $this->getName());
    return $result;
  }

  /**
   * Remove all assets from a drop
   *
   * @return <type>
   */
  public function emptyDrop()
  {
    $result = $this->request('PUT', 'drops/' . $this->_origName . '/empty');
    $this->_assets = null;
    return $result;
  }


 /**
   *
   * @return string An HTML string containgin a smiple form upload.
   */
  public function getSimpleUploadForm()
  {
    $docroot = "http://".$_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

    $params = array(
      'api_key'     => $this->getApiKey(),
      'drop_name'   => $this->_origName,
      'format'      => 'json',
      'redirect_to' => $docroot,
      'version'     => '3.0'
    );

    $params = $this->_signIfNeeded($params);
    $input='';
    foreach ($params as $k=>$v)
      $input .= "<input type=\"hidden\" name=\"$k\" value=\"$v\"/>\n";

    $html = <<<EOF
    <form action="http://up.rmb.io/upload" method="post" enctype="multipart/form-data">
      <ul>
        <li>
          <label for="file">Add a new file:</label>
          <input type="file" name="file" size="25"/>
        </li>
        <li>
          $input
          <input type="submit" value="submit"/>
        </li>
      </ul>
    </form>

EOF;

    return $html;
  }

  /**
  *
  *   - srcdir              the source directory that holds the uploadify files.
  *                         Default to /uploadify/
  *
  * Options are:
  *   - comment 
  *   - description
  *   - redirect_to
  *   - convert_to      
  *   - output_locations
  *   - pingback_url        the url of a pingback server
  *  
  */
  public function getUploadifyForm($srcdir=null,$options=null)
  {

    $upload_url = self::UPLOAD_URL;

    $docroot = "http://".$_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

    $params = array(
      'api_key'     => $this->getApiKey(),
      'drop_name'   => $this->_origName,
      'format'      => 'json',
      'version'     => '3.0'
    );
    
    # Process the optional parameters
    if (!($options == NULL)) {
      foreach ($options as $k => $v) { $params[$k] = $v; }
    }

    $params = $this->_signIfNeeded($params);

    $str= json_encode($params);

    $html =<<<EOL
		<script type="text/javascript" src="$srcdir/uploadify/jquery-1.3.2.min.js"></script>
		<script type="text/javascript" src="$srcdir/uploadify/swfobject.js"></script>
		<script type="text/javascript" src="$srcdir/uploadify/jquery.uploadify.v2.1.0.min.js"></script>
		<link rel="stylesheet" type="text/css" media="screen, projection" href="$srcdir/uploadify/uploadify.css" />

		<script type="text/javascript">// <![CDATA[
		$(document).ready(function() {
		$('#file').uploadify({
		'uploader'  : '$srcdir/uploadify/uploadify.swf',
		'script'    : '$upload_url',
		'multi'     : true,
		'scriptData': $str,
		'cancelImg' : '$srcdir/uploadify/cancel.png',
		'auto'      : true,
        /* TODO: This is not quite working right now
        'onComplete': function(e,q,f,response,d){
            //var j = eval('(' + response + ')');
            jQuery.post('ajax/upload_complete.php', { drop_name:  '$this->_origName', response: response });
            },*/
		'onAllComplete' : function(){setTimeout(window.location = '$docroot',1000);},
		'onError'   : function(e, q, f, o) { alert("ERROR: " + o.info + o.type); }, 
		'folder'    : '/uploads'
		});
		});
		// ]]></script>

    <input type="file" name="fileUpload" id="file"/>
EOL;

    return $html;
  }

  /**
   * Create a drop
   *
   * @link http://docs.rmb.io/Create-a-Drop
   * @param <type> $dropname
   * @return <type>
   */
  public function createDrop($name, $max_size=null, $description=null, $email_key=null, $chat_password=null)
  {
    return $this->setName($name)->
            setMaxSize($max_size)->
			setDescription($description)->
			setEmailKey($email_key)->
			setChatPassword($chat_password)->
            save();
  }

  /**
   * Get a instance of a Drop object that is useful for chaining.
   *
   * @param <type> $api_key
   * @param <type> $api_secret
   * @return Rmb_Api
   */
  public static function getInstance($api_key=null,$api_secret=null)
  {
    return new Rmb_Drop($api_key, $api_secret);
  }

  /**
   * Getter methods for response bodies
   */
  public function getAdminToken()     { return $this->_values['admin_token']; }
  public function getAssetCount()     { return $this->_values['asset_count']; }
  public function getChatPassword()   { return $this->_values['chat_password']; }  
  public function getCurrentBytes()   { return $this->_values['current_bytes']; }
  public function getDescription()    { return @$this->_values['description']; }
  public function getEmail()          { return $this->_values['email']; }
  public function getEmailKey()       { return $this->_values['email_key']; }
  public function getMaxBytes()       { return $this->_values['max_bytes']; }
  public function getMaxSize()        { 
	return (!empty($this->_values['max_size']) ? $this->_values['max_size'] : $this->_values['max_bytes'] / 1024 / 1024); 
  }
  public function getName()           { return $this->_values['name']; }

  /**
   * Setter methods for updates / new drops
   *
   * @param string A description of the drop.
   */
  public function setDescription($description) {
    if(!empty($description)){ $this->_values['description'] = $description; }
    return $this;
  }

  /*
   * Set the drop name
   *
   * @param string The name of the drop
   */
  public function setName($name)
  {
    if(!empty($name)){ 
		$this->_values['name'] = $name;
		# Set the original name if it has not yet been set
		$this->_origName = (is_null($this->_origName)) ? $name : $this->_origName; 
	}
    return $this;
  }

  public function setMaxSize($max_size=null)
  {
	  //default to a petabyte - 1073741824 megs. Pratically, this is 'unlimited'
	  $max_size = (empty($max_size) ? 1073741824 : $max_size); 
      $this->_values['max_size'] = $max_size;
      return $this;
  }

  public function setEmailKey($email_key=null){
	if(!empty($email_key)){ $this->_values['email_key']; }
	return $this;
  }

  public function setChatPassword($chat_password=null){
	if(!empty($chat_password)){ $this->_values['chat_password']; }
	return $this;
  }


################################################################################
#  Methods that deal with Assets
################################################################################

  /**
   * Return all assets from a drop
   *
   * @return mixed
   */
  private function loadAssets()
  {
    $assets = $this->request('GET', 'drops/' . $this->_origName . '/assets',array());

    # Loop over each asset in the drop and create a pre-loaded object
    foreach($assets['assets'] as $a)
    {
      $arr = new Rmb_Asset($this->getApiKey(),$this->getApiSecret());
      $arr->setName($a['name'])
        ->setDropName($this->getName())
        ->setValues($a)
        ->setRoles($a['roles']);
      $this->_assets[] = $arr;
    }

    return $this;
  }

  /**
   * Return the array of asset objects to the calling operation
   *
   * @return <type>
   */
  public function getAssets()
  {
    if (empty($this->_assets))
      $this->loadAssets();

    return $this->_assets;
  }

  /**
   * Retrieve a single asset from a drop
   *
   * @param string $asset_name
   * @return mixed A single Asset object
   */
  public function getAsset($asset_name = null)
  {
    $result = $this->request('GET', 'drops/' . $this->getName() . "/assets/$asset_name",array());

    # TODO - move this to a helper method
    $arr = new Rmb_Asset($this->getApiKey(),$this->getApiSecret());
    $arr->setName($result['name'])
        ->setDropName($this->getName())
        ->setValues($result)
        ->setRoles($result['roles']);

    return $arr;

  }

  public function promoteNick() {}

  # Subscriptions - # TODO - implement these method stubs
  
  
  public function getSubscriptions() 
  {
    return $this->request('GET', "drops/{$this->getName()}/subscriptions",array());
  }

  /**
   *
   * @param <type> $sub_id
   * @return <type>
   */
  public function getSubscription($sub_id)
  {
    return $this->request('GET', "drops/{$this->getName()}/subscriptions/$sub_id",array());
  }

  /**
   *
   * @param <type> $type
   * @param <type> $events
   * @param <type> $options
   */
  public function createSubscription($type,$events=array('asset_added'),$options=null) 
  {

    $params = array('type'=>$type);

    # Process the optional parameters
    foreach ($options as $k=>$v)
      $params[$k] = $v;

    foreach($events as $v)
      $params[$v] = true;

    $result = $this->request('POST', "drops/{$this->getName()}/subscriptions", $params);
  }

  /**
   *
   * @param <type> $sub_id
   * @return <type>
   */
  public function deleteSubscription($sub_id)
  {
    return $this->request('GET', "drops/{$this->getName()}/subscriptions/$sub_id",array());
  }


  /**
   * Tell us whether 
   *
   * @return boolean True if the drop is loaded, false if not loaded
   */
  public function isLoaded()
  {
    return $this->_is_loaded;
  }


  
}
