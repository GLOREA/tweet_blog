<?php
require_once dirname(__FILE__)."/TweetBlogConfig.php";
require_once dirname(__FILE__)."/TweetBlogDB.php";
require_once dirname(__FILE__)."/lib/twitteroauth/autoloader.php";
require_once dirname(__FILE__)."/Tweets.php";
require_once dirname(__FILE__)."/TweetBlogDB.php";

class TweetAnalyzer{
	protected $twitter;
	protected $tweets;
	protected $access_token;

	protected $hashtags;

	public function __construct($init_access_token = false){
		$this->save_access_token();
		$access_token = $this->load_access_token();

		if($access_token){
			$this->twitter = $this->generate_twitter_instance($access_token);
		}else{
			if($init_access_token){ $this->get_oauth_token(); }
		}
		$this->tweets = new Tweets();
		$this->hashtags = null;
		$this->registered_max_tweet_id = null;
	}

	public function db_setup($is_force = false){
		TweetBlogDB::table_setup($is_force);
		$this->tweets->reload();
	}

	public function register($all_regist = false){
		$this->tweet_register($all_regist);
	}

	protected function tweet_register($all_regist = false){
		try{
			if(! $this->is_twitter_connect()){ return false; }
			$params = array();
			if($all_regist){
				$tweets = array();
				for($i = 0; $i < 15; $i++){ // 3000件までしか担保されてないため、200 * 15 = 3000 で無限ループ防止
					$tweets_buf = $this->get_tweets($params);
					if(empty($tweets_buf)) { break; }
					$params['max_id'] = end($tweets_buf)->id;
					reset($tweets_buf);
					$tweets = array_merge($tweets, $tweets_buf);
				}
			}else{
				$since_id = $this->tweets->registered_max_tweet_id();
				if(isset($since_id) && $since_id > 0) { $params['since_id'] = $since_id; }
				$tweets = $this->get_tweets($params);
			}
			foreach($tweets as $tweet){
				if(!is_null($tweet->in_reply_to_status_id)) continue; // リプライツイートは無視する
				$this->tweets->add_tweet($tweet);
			}
			return $this->tweets->save();
		}catch(Exception $e){
			// HACK: エラー処理
			trigger_error('Tweet 取得ミスった');
		}
	}

	public function is_twitter_connect(){
		return ! is_null($this->twitter);
	}

	protected function get_tweets($params = array()){
		$params = array_merge(
			array(
				'screen_name' => TweetBlogConfig::screen_name(),
				'count' => 200,
				'exclude_replies' => true
			), $params
		);
		$result = $this->twitter->get('statuses/user_timeline', $params);
		if(isset($result->errors)){
			trigger_error($result->errors->message);
			return null;
		}
		return $result;
	}

	protected function load_access_token(){
		if(!file_exists(TweetBlogConfig::twitter_access_token())){ return null; }

		$acess_token = explode('<>', file_get_contents(TweetBlogConfig::twitter_access_token()));
		return array('access_token' => $acess_token[0], 'access_token_secret' => $acess_token[1]);
	}

	protected function save_access_token(){
		if(!array_key_exists('oauth_token', $_REQUEST) || is_null($_REQUEST['oauth_token'])){ return null; }

		$twitter = $this->generate_twitter_instance();
		$access_token = $twitter->oauth("oauth/access_token", $_REQUEST);

		return file_put_contents(TweetBlogConfig::twitter_access_token(), $access_token['oauth_token'] . '<>' .$access_token['oauth_token_secret'], LOCK_EX);
	}

	protected function get_oauth_token(){
		print "Generater twitter instance.";
		$twitter = $this->generate_twitter_instance();

		print "Get request token hedder.";
		$request_token = $twitter->oauth("oauth/request_token", array("oauth_callback" => TweetBlogConfig::oauth_callback()));
		$url = $twitter->url("oauth/authorize", $request_token);

		print "Send header.";
		header('Location: ' . $url);
	}

	protected function generate_twitter_instance($access_token = null){
		if(is_null($access_token)){
			return new Abraham\TwitterOAuth\TwitterOAuth(
							TweetBlogConfig::consumer_key(),	// consumerKey
							TweetBlogConfig::consumer_secret()	// consumerSecret
						);
		}
		return new Abraham\TwitterOAuth\TwitterOAuth(
						TweetBlogConfig::consumer_key(),						// consumerKey
						TweetBlogConfig::consumer_secret(),						// consumerSecret
						$access_token['access_token'],
						$access_token['access_token_secret']
					);
	}
}
