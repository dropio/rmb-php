<?php

include_once('Api.php');
include_once('Asset.php');

Class Dropio_Drop_Exception extends Dropio_Exception{};

/**
 * Dropio_Drop is used to access all functionality related to a Drop.  Most 
 * methods are chainable, allowing for the the creation of a drop to happen
 * inline.  
 * 
 * For example, to create a drop and to access it.
 * 
 * $drop = Dropio_Drop::instance('dropname')->save();
 * 
 * To load a pre-existing drop and load:
 * 
 * $drop = Dropio_Drop::load('dropname');
 * 
 */

Class Dropio_Drop extends Dropio_Api {

  private $_origName = null;
  private $_token    = null;
  private $_assets   = array();  # Array of asset objects

  /**
   * @var boolean Has a drop already been loaded?
   */
  private $_is_loaded    = false;


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
    return array(
      'name'        => $this->getName(),
      'description' => $this->getDescription()
    );
  }

  /**
   * Save data back to a drop
   */
  public function save()
  {
    # Is this new or an update? If _is_loaded is false, the it is new. Otherwise
    # we are updating an existing drop
    if (!$this->_is_loaded) {
      # We'll create a new one.
      if ($this->getName() == NULL)
        $result = $this->request('POST', 'drops', array());
      else
        $result = $this->request('POST', 'drops', array('name'=>$this->getName()));
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
   * @link http://backbonedocs.drop.io/Delete-a-Drop
   * @return mixed
   */
  public function delete()
  {
    $result = $this->request('DELETE','drops/' . $this->getName(), array());
    return $result;
  }

  /**
   * Remove all assets from a drop
   *
   * @return <type>
   */
  public function emptyDrop()
  {
    $result = $this->request('PUT', 'drops/' . $this->_origName . '/empty', array());
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
    <form action="http://assets.drop.io/upload" method="post" enctype="multipart/form-data">
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

  public function getUploadifyForm()
  {
    $upload_url = self::UPLOAD_URL;

    $docroot = "http://".$_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

    $params = array(
      'api_key'     => $this->getApiKey(),
      'drop_name'   => $this->_origName,
      'format'      => 'json',
      'version'     => '3.0'
    );

    $params = $this->_signIfNeeded($params);

    $str= json_encode($params);

    $html =<<<EOL
		<script type="text/javascript" src="../uploadify/jquery-1.3.2.min.js"></script>
		<script type="text/javascript" src="../uploadify/swfobject.js"></script>
		<script type="text/javascript" src="../uploadify/jquery.uploadify.v2.1.0.min.js"></script>
		<link rel="stylesheet" type="text/css" media="screen, projection" href="../uploadify/uploadify.css" />

		<script type="text/javascript">// <![CDATA[
		$(document).ready(function() {
		$('#file').uploadify({
		'uploader'  : '../uploadify/uploadify.swf',
		'script'    : '$upload_url',
		'multi'    : true,
		'scriptData': $str,
		'cancelImg' : '../uploadify/cancel.png',
		'auto'      : true,
		'onAllComplete' : function(){setTimeout(window.location = '$docroot',3000);},
		'folder'    : '/uploads'
		});
		});
		// ]]></script>

    <input type="file" name="fileUpload" id="file"/>
EOL;

    return $html;
  }

  public function createDrop($dropname)
  {
    return $this->setName($dropname)->save();
  }

  /**
   * Get a instance of a Drop object that is useful for chaining.
   *
   * @param <type> $api_key
   * @param <type> $api_secret
   * @return Dropio_Api
   */
  public static function getInstance($api_key=null,$api_secret=null)
  {
    return new Dropio_Drop($api_key, $api_secret);
  }

  /**
   * Getter methods for response bodies
   */
  public function getChatPassword()   { return $this->_values['chat_password']; }
  public function getAdminToken()     { return $this->_values['admin_token']; }
  public function getAssetCount()     { return $this->_values['asset_count']; }
  public function getCurrentBytes()   { return $this->_values['current_bytes']; }
  public function getExpirationLength() { return $this->_values['expiration_length']; }
  public function getEmail()          { return $this->_values['email']; }
  public function getDescription()    { return @$this->_values['description']; }
  public function getMaxBytes()       { return $this->_values['max_bytes']; }
  public function getName()           { return $this->_values['name']; }
  public function getExpiresAt()      { return $this->_values['expires_at']; }

  /**
   * Setter methods for updates / new drops
   */
  public function setDescription($description) {
    $this->_values['description'] = $description;
    return $this;
  }

  /*
   * Set the drop name
   *
   * @param string The name of the drop
   */
  public function setName($name)
  {
    $this->_values['name'] = $name;
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
      $arr = new Dropio_Asset($this->getApiKey(),$this->getApiSecret());
      $arr->setName($a['name'])
        ->setDropName($this->getName())
        ->setValues($a)
        ->setRoles($a['roles']);
      $this->_assets[] = $arr;
    }

    # TODO - this should iterate over the list and create an array of asset objects

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
    $arr = new Dropio_Asset($this->getApiKey(),$this->getApiSecret());
    $arr->setName($result['name'])
        ->setDropName($this->getName())
        ->setValues($result)
        ->setRoles($result['roles']);

    return $arr;

  }

  public function promoteNick() {}

  # Subscriptions
  public function getSubscriptions() {}
  public function getSubscription() {}
  public function createSubscription() {}
  public function deleteSubscription() {}

  public function isLoaded()
  {
    return $this->_is_loaded;
  }
}
