<?php

/**
 * You can execute this by running: php example.php
 * 
 * To view your changes, open: 
 *     http://drop.io/php_api_lib
 * 
 */

include('Dropio/Api.php');

//Visit http:/api.drop.io to get an api_key.
Dropio_Api::setKey(API_KEY);

Dropio_Drop::load('php_api_lib')->addNote('This is an example of the Drop.io PHP Library','Hello World');

//Below is an example of a one line uploade to a drop.
Dropio_Drop::load('php_api_lib')->addFile(FILE_NAME);

//An example of looping through all the assets in a drop.
$page = 1;

while ( ($assets = Dropio_Drop::load('php_api_lib')->getAssets($page)) && $assets->getCount()) {
  foreach ($assets as $name=>$asset) {
    echo $asset->name . '. ' . $asset->contents . "\n";
  }
  $page++;
}

// Using the Dropio_Api() class directly
try {
  $api = new Dropio_Api(API_KEY);
  $response = $api->request('GET', '/drops/php_api_lib');
  print_r($response);
} catch (Dropio_Api_Exception $e) {
  die("Error:" . $e->getMessage());
}