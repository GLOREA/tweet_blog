<?php
class TweetBlogConfig {
	// 変更が効かない構造体が欲しかったためクラス化。非常に馬鹿らしい…
	private static $consumer_key         = "SXN2rBkFs2qpB25GrnAsCQlTR";
	private static $consumer_secret      = "tP7jvUVel4usJef2ksSvSlBTOK6gXdLnROMe8G5pY5yVvU2YwW";
	private static $twitter_access_token = "/access_token.php";

	private static $screen_name = 'circle_glorea';

	private static $oauth_callback ="http://google.com/";

	private static $db_host   = "db_host";
	private static $db_scheme = "scheme_name";
	private static $db_user   = "username";
	private static $db_pass   = "password";

	private static $media_dir = "/media/";

	// 以下、設定とは無関係

	public static function setup($callback_url = null){
		if(isset($callback_url)) { self::$oauth_callback = $callback_url; }
		self::$twitter_access_token = dirname(__FILE__) . self::$twitter_access_token;
		self::$media_dir            = dirname(__FILE__) . '/..' . self::$media_dir;
	}

	public static function __callStatic($name, $args){
		$values = get_class_vars(get_called_class());
		if(array_key_exists($name, $values)){
			return $values[$name];
		}else{
			$e = new Exception;
			$error = array_shift($e->getTrace());
			trigger_error('Undefined member: ' . get_called_class() . '::' . $name . '() in ' . $error['file'] . ' on line ' . $error['line']);
		}
	}
}

// 読み込み時に必ずsetup を行う
TweetBlogConfig::setup();
