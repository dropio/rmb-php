<?php

/**
 * Enter description here...
 *
 */

Class Dropio_Asset_Comment extends Dropio_Data {

  var $asset = null;
  var $primary_key = 'id';

  /**
   * Enter description here...
   *
   * @param Dropio_Asset $asset
   * @param integer $comment_id
   */

  function __construct ( Dropio_Asset &$asset, $comment_id = null ) {

    $this->asset = $asset;
    $this->values[$this->primary_key] = $comment_id;

  }

  /**
   * Enter description here...
   *
   * @return Dropio_Asset_Comment
   */

  function save () {

    if (!$this->loaded) {

      $this->values['token'] = $this->asset->token;

      $result = $this->asset->dropio_api->request('POST','drops/' . $this->asset->drop->name . '/assets/' . $this->asset->name . '/comments',
      $this->values
      );

      $this->values = Array();

      foreach ($result as $var=>$value) {
        $this->$var = $value;
      }

      $this->loaded = true;

      return $this;

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

      $result = $this->asset->dropio_api->request(
      'PUT',
      'drops/' . $this->asset->drop->name . '/assets/' . $this->asset->name . '/comments/' .  $this->values[$this->primary_key],
      $this->values
      );

      return $this->loadFromArray($result);
    }

  }

}