<?php

/**
 * Object that represents the comment that is attached to an asset.
 * 
 * 
 * For example: 
 *  
 *  foreach ($asset->getComments($page) as $comment) {
 *    echo $comment->contents; //Dropio_Asset_Comment
 *    }
 *    
 *
 */

Class Dropio_Asset_Comment extends Dropio_Data {

  var $asset = null;
  var $primary_key = 'id';

  /**
   * Constructor takes the asset where the comment is attached to.
   *
   * @param Dropio_Asset $asset
   * @param integer $comment_id
   */

  function __construct ( Dropio_Asset &$asset, $comment_id = null ) {

    $this->asset = $asset;
    $this->values[$this->primary_key] = $comment_id;

  }

  /**
   * Save method that does the actual write back to the API
   *
   * @return Dropio_Asset_Comment
   */

  function save () {

    if (!$this->loaded) {

      $this->values['token'] = $this->asset->token;

      $result = $this->asset->dropio_api->request(
      'POST',
      'drops/' . $this->asset->drop->name . '/assets/' . $this->asset->name . '/comments',
      $this->values
      );

      $this->values = Array();

      foreach ($result as $var=>$value) {
        $this->$var = $value;
      }

      $this->loaded = true;

      return $this;

    } else {
      
      //Updating

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
      'drops/'.$this->asset->drop->name.'/assets/'.$this->asset->name.'/comments/'. $this->values[$this->primary_key],
      $this->values
      );

      return $this->loadFromArray($result);
      
    }

  }

}