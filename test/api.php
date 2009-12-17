<?php
require_once 'PHPUnit/Framework.php';
include('../Dropio/Api.php');

if (!isset($_ENV['DROPIO_API_KEY']))
  throw new Exception('Set the DROPIO_API_KEY environment variable: export DROPIO_API_KEY=xxx');

Dropio_Api::setKey($_ENV['DROPIO_API_KEY']);

//Make this at least 31
define('TEST_ITERATIONS', 31);

class Api_TestCase extends PHPUnit_Framework_TestCase {

  public function testComments () {

    $drop = Dropio_Drop::instance()->save();

    $asset = $drop->addNote('This is a note', 'yes it is');

    $com = Array();

    for ($a = 0;$a < TEST_ITERATIONS; $a++) {
      $comment = $asset->addComment(md5($a));
      $com[$comment->id] = $a;
    }

    $page = 1;

    while ( ($comments = $asset->getComments($page)) && $comments->getCount()) {
      foreach ($comments as $comment) {
        $i = $com[$comment->id];

        $this->assertEquals(md5($i), $comment->contents);

        $comment->contents = md5($i+100);
        $comment->save();

      }

      $page++;
    }


    /**
     * Checks to see if the comments have been updated
     */

    $page = 1;
    
    while ( ($comments = $asset->getComments($page)) && $comments->getCount()) {
      foreach ($comments as $comment) {
        $i = $com[$comment->id];
        $this->assertEquals(md5($i+100), $comment->contents);
      }

      $page++;
    }

  }

  public function testSubscriptions () {

    $sub = Array();

    $drop = Dropio_Drop::instance()->save();

    for ($a = 0;$a < TEST_ITERATIONS; $a++) {
      $subscription = Dropio_Drop_Subscription::instance(
      $drop
      )
      ->set('type', 'pingback')
      ->set('url', 'http://example.org/' . md5($a))
      ->save();

      $sub[$subscription->id] = $a;
    }

    $i    = 0;
    $page = 1;

    while ( ($subscriptions = $drop->getSubscriptions($page)) && $subscriptions->getCount()) {
      foreach ($subscriptions as $subscription) {
        $i = $sub[$subscription->id];
        $this->assertEquals('http://example.org/' . md5($i), $subscription->url);
        $this->assertEquals('pingback', $subscription->type);

      }

      $page++;
    }

  }
  public function testDrop () {

    $drop = Dropio_Drop::instance()->save();

    //Should have a name of 7 or more chars, put 4 here to be safe
    $this->assertGreaterThan(4, strlen($drop->name));

    //Lets add notes
    for ($a = 0;$a < TEST_ITERATIONS; $a++) {
      $asset = $drop->addNote(sha1($a), md5($a));

      $this->assertEquals(md5($a), $asset->name);
      $this->assertEquals(sha1($a), $asset->contents);

    }

    //Test Loading a drop
    $drop2 = Dropio_Drop::load($drop->name);
    $this->assertEquals($drop2->name, $drop->name);

    $i    = 0;
    $page = 1;

    while ( ($assets = $drop->getAssets($page)) && $assets->getCount()) {
      foreach ($assets as $name=>$asset) {
        $this->assertEquals(md5($i),  $asset->name);
        $this->assertEquals(sha1($i), $asset->contents);

        $i++;
      }
      $page++;
    }
    $this->assertEquals($i, TEST_ITERATIONS);
  }

}
