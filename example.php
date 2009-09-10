<?php

////
// You can execute this by running: php example.php
////

include('Dropio/Api.php');
include('Dropio/Data.php');
include('Dropio/Drop.php');
include('Dropio/Manager.php');
include('Dropio/Asset.php');
include('Dropio/Drop/Subscription.php');
include('Dropio/Asset/Comment.php');

//Visit http:/api.drop.io to get an api_key.
Dropio_Api::setKey(API_KEY);

Dropio_Drop::load('php_api_lib')->addNote('This is an example of the Drop.io PHP Library','Hello World');

//Uncomment this line to upload this php file. Whoa, how meta.
// Dropio_Drop::load('php_api_lib')->addFile($_SERVER["SCRIPT_NAME"]);
