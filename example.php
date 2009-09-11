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

//Uncomment this line to upload this php file. Whoa, how meta.
//Dropio_Drop::load('php_api_lib')->addFile($_SERVER["SCRIPT_NAME"]);


// Using the Dropio_Api() class directly
/*
try {
  $api = new Dropio_Api(API_KEY);
  $response = $api->request('GET', '/drops/php_api_lib');
  print_r($response);

} catch (Dropio_Api_Exception $e) {
  die("Error:" . $e->getMessage());
}
*/