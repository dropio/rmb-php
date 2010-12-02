<?php

require_once('Rmb/Drop.php');
$API_KEY = "348df82fc24bd1eff27084a7b5f876afe61d9d7b";
$dropname = 'waxhammer';
 //Rmb_Drop::getInstance($API_KEY)
$drop = Rmb_Drop::getInstance($API_KEY)->load($dropname);
//var_dump($drop);
$assets = $drop->getAssets(1);
print "First asset name ------------- \r\n" .$assets[0]->getName();
//var_dump($assets);
?>