<?php

/**
 * Enter description here...
 *
 */

Class Dropio_Asset extends Dropio_Data {

  var $drop = null;

  /**
   * Enter description here...
   *
   * @return Dropio_Asset
   */

  function save() {

    $result = $this->dropio_api->request(
    'PUT',
    'drops/'.$this->drop->name.'/assets/'.$this->values[$this->primary_key],
    $this->values
    );

    foreach ($result as $var=>$value) {
      $this->$var = $value;
    }

    $this->loaded = true;

    return $this;

  }

  /**
   * Deletes the asset.
   *
   * @return Dropio_Asset
   */

  function delete() {

    if (!strlen($this->values[$this->primary_key])) {
      throw new Dropio_Exception("Asset name must be set in order to delete");
    }

    return $this->dropio_api->request(
    'DELETE',
    'drops/'.$this->drop->name.'/assets/'.$this->values[$this->primary_key],
    Array('token'=>$this->drop->token())
    );

  }

  /**
   * Enter description here...
   *
   * @return Dropio_Asset
   */

  static function instance () {
    return new Dropio_Asset();
  }

  /**
	 * Return the embed code for an asset.
	 *
	 * @return string
	 */

  function embedCode () {

    $result = $this->dropio_api->request(
    'GET',
    'drops/'.$this->drop->name.'/assets/'.$this->values[$this->primary_key].'/embed_code',
    Array('token'=>$this->drop->token())
    );

    return $result['embed_code'];

  }

  /**
	 * Copies an asset to another drop.
	 *
	 * @param string $drop_name
	 * @param string $drop_token
	 */

  function copyTo ( $drop_name, $drop_token = null ) {

    $result = $this->dropio_api->request('POST','drops/' . $this->drop->name . '/assets/' . $this->values[$this->primary_key] . '/copy',
    Array(
    'token'      => $this->drop->token(),
    'drop_name'  => $drop_name,
    'drop_token' => $drop_token
    )
    );

    return $result;

  }

  /**
	 * Move an asset to another drop.
	 *
	 * @param string $drop_name
	 * @param string $drop_token
	 */

  function moveTo ( $drop_name, $drop_token = null ) {

    $result = $this->dropio_api->request('POST','drops/' . $this->drop->name . '/assets/' . $this->values[$this->primary_key] . '/move',
    Array(
    'token'=>$this->drop->token(),
    'drop_name'=>$drop_name,
    'drop_token'=>$drop_token
    )

    );

  }

  /**
	 * Add comment to an asset.
	 *
	 * @param string $comment_text
	 * @return Dropio_Asset_Comment
	 */

  function addComment ( $comment_text ) {

    $comment = new Dropio_Asset_Comment ($this);
    $comment->contents = $comment_text;

    return $comment->save();

  }


  /**
   * Returns a Dropio_Asset_Comment_Set of comments.
   *
   * @param integer $page
   * @return Dropio_Drop
   */

  function getComments ( $page = 1) {

    $result = $this->dropio_api->request('GET', 'drops/' . $this->drop->name . '/assets/' . $this->values[$this->primary_key] . '/comments',
    Array(
    'page'=>$page,
    'token'=>$this->drop->token(),
    'order'=>$order
    )
    );

    $comments = Array();

    foreach ( $result['comments'] as $comment_array) {
      $comment = new Dropio_Asset_Comment($this);
      $comments[ $comment_array['id'] ] = $comment->loadFromArray($comment_array);
    }

    return new Dropio_Asset_Comment_Set($comments,$result['total'], $result['page'], $result['per_page'], 'id');

  }



  /**
	 * Send an asset to a fax.
	 *
	 * @param string $fax_number
	 * @return Dropio_Asset
	 */

  function sendToFax ( $fax_number ) {

    $result = $this->dropio_api->request('POST','drops/' . $this->drop->name . '/assets/' . $this->values[$this->primary_key] . '/send_to',
    Array(
    'token'			=> $this->drop->token,
    'fax_number' => $fax_number,
    'medium'=>'fax'
    )

    );

    return $this;

  }

  /**
	 * Send an asset as an email
	 *
	 * @param string $emails
	 * @param string $message
	 * @return Dropio_Asset
	 */

  function sendToEmail ( $emails, $message = null ) {

    if (is_array($emails)) {
      $emails = implode(',', $emails);
    }

    $result = $this->dropio_api->request('POST','drops/' . $this->drop->name . '/assets/' . $this->values[$this->primary_key] . '/send_to',
    Array(
    'token'=>$this->drop->token(),
    'emails'=>$emails,
    'message'=>$message,
    'medium'=>'email'
    )

    );

    return $this;

  }

}