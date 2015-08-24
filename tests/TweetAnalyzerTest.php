<?php
class TweetAnalyzerTest extends PHPUnit_Framework_TestCase {
	public static $tweet_analyzer;
	public static function setUpBeforeClass(){
		self::$tweet_analyzer = new TweetAnalyzer(true);
	}

	public function testDBSetup(){
		self::$tweet_analyzer->db_setup(true);
		self::$tweet_analyzer->db_setup(false);
	}
}
