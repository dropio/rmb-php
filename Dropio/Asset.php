<?php

include_once('Drop.php');

Class Dropio_Asset extends Dropio_Api {
  /**
   *
   * @var <type> The name of the drop owning the asset
   */
  private $_dropName  = null;

  private $_origName  = null;
  /**
   *
   * @var <type> The name of the asset
   */
  private $_name      = null;

  /**
   *
   * @var array An array of role objects
   */
  private $_roles     = array();

  private $_is_loaded  = false;

    /**
   * Get a instance of an Asset object that is useful for chaining.
   *
   * @param <type> $api_key
   * @param <type> $api_secret
   * @return Dropio_Asset
   */
  public static function getInstance($api_key=null,$api_secret=null)
  {
    return new Dropio_Asset($api_key, $api_secret);
  }

  public function load()
  {
    $this->setValues($this->request('GET', "drops/".$this->getDropName()."/assets/".$this->getName(), array()));
    $this->_origName = $this->getName();
    $this->_is_loaded = true;
    return $this;

  }

    /**
   * Save data back to an asset
   */
  public function save()
  {
    $result = $this->request('PUT', 'drops/' . $this->getDropName() . '/assets/' . $this->getName() , $this->prepareUpdate());

    $this->setValues($result);
    $this->_origName = $this->getName();
    return $this;
  }

  private function prepareUpdate()
  {
    if ($this->getTitle() !== NULL)
      $params['title'] = $this->getTitle();
    if ($this->getDescription() !== null)
      $params['description'] = $this->getDescription();
    if ($this->getUrl() !== NULL)
      $params['url'] = $this->getTitle();
    if ($this->getContents() !== NULL)
      $params['contents'] = $this->getContents();

    return $params;

  }

  public function setDropName($name)
  {
    $this->_dropName = $name;
    return $this;
  }

  public function setName($name)
  {
    $this->_name = $name;
    return $this;
  }

  public function setDescription($desc)
  {
    $this->_values['description'] = $desc;
    return $this;
  }


  public function createLink($title, $url, $description=null, $comment=null)
  {
    $params = array(
      'title' => $title,
      'url'   => $url
    );

    if (!empty($description))
      $params['description'] = $description;

    if (!empty($comment))
      $params['comment'] = $comment;

    return $this->request('POST','drop/' . $this->_dropName . '/assets',$params);

  }

  /**
   *
   * @link http://backbonedocs.drop.io/Create-a-Note
   *
   * @param <type> $content
   * @param <type> $title
   * @param <type> $description
   * @param <type> $comment
   * @return <type>
   */
  public function createNote($content, $title=null,$description=null, $comment=null)
  {
    $params = array(
      'title' => $title,
      'url'   => $url
    );

    if (!empty($description))
      $params['description'] = $description;

    if (!empty($comment))
      $params['comment'] = $comment;

    return $this->request('POST','drop/' . $this->_dropName . '/assets',$params);

  }


  /**
   *
   * @param <type> $url
   * @param <type> $description
   * @param <type> $convert_to
   * @param <type> $ping_back
   * @return <type>
   */
  public function createFileFromUrl($url,$description=null,$convert_to=null,$ping_back=null)
  {
    # Required params
    $params = array(
      'url'   => $url
    );

    if (!empty($description))
      $params['description'] = $description;

    if (!empty($convert_to))
      $params['convert_to'] = $convert_to;

    if (!empty($ping_back))
      $params['ping_back'] = $ping_back;

    return $this->request('POST','drop/' . $this->_dropName . '/assets',$params);

  }

  /**
   *
   * @param <type> $storage_location
   * @param <type> $storage_key
   * @param <type> $filename
   * @param <type> $description
   * @param <type> $comment
   * @return <type>
   */
  public function createAssetFromStorageLocation($storage_location,$storage_key,$filename,$description=null,$comment=null)
  {
    # Required params
    $params = array(
      'storage_location'   => $storage_location,
      'storage_key'        => $storage_key,
      '$filename'          => $filename
    );

    if (!empty($description))
      $params['description'] = $description;

    if (!empty($comment))
      $params['comment'] = $comment;

    return $this->request('POST','drop/' . $this->_dropName . '/assets',$params);

  }

  public function uploadFile(){}
  public function downloadOriginalFile() {}
  public function getEmbedCode() {}
  public function updateAsset() {}
  public function addFileToAsset() {}
  public function removeFileFromAsset() {}

  /**
   * Delete an asset
   *
   * @link http://backbonedocs.drop.io/Delete-an-Asset
   * @return mixed
   */
  public function delete()
  {
    $result = $this->request('DELETE','drops/' . $this->_dropName . '/assets/' . $this->getName(), array());
    return $result;
  }
  public function sendAsset() {}
  public function copyAsset() {}
  public function moveAsset() {}

################################################################################
#  Methods that deal with Comments
################################################################################
  public function getListOfComments() {}
  public function getComment() {}
  public function createComment() {}
  public function updateComment() {}
  public function deleteComment() {}

################################################################################
#  Getters for response bodies
################################################################################
  public function getDropName()       { return $this->_dropName; }
  public function getType()           { return $this->_values['type']; }
  public function getTitle()          { return $this->_values['title']; }
  public function getDescription()    { return $this->_values['description']; }
  public function getCreatedAt()      { return $this->_values['created_at']; }
  public function getName()           { return $this->_name; }
  public function getUrl()            { return $this->_values['url']; }
  public function getContents()       { return $this->_values['contents']; }

  /**
   *
   * @return array Get all roles for an asset
   */
  public function getRoles()          { return $this->_values['roles']; }

  /**
   *  Retrieve a single role for an asset (original_content, thumbnail,
   *  small_thumbnail, large_thumbnail, or web_preview)
   *
   * @param string $name The name of the role
   * @return array Retrieve a single role from an asset
   */
  public function getRole($name)
  {
    foreach($this->_roles as $r)
      if ($r['name'] == $name)
        return $r;
  }

  /**
   *
   * @param <type> $roles
   * @return <type>
   */
  public function setRoles($roles=array())
  {
    $this->_roles = $roles;
    return $this;
  }

  /**
   *
   * @param <type> $role
   * @return <type>
   */
  public function getFileUrl($role='thumbnail')
  {
    foreach($this->_roles as $r)
      if ($r['name'] == $role)
        return $r['locations'][0]['file_url'];
  }

}
