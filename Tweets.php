<?php
require_once dirname(__FILE__)."/Tweet.php";
class Tweets{
	protected $tweets;
	protected $registered_max_tweet_id;

	public function __construct($is_load = true){
		$this->tweets = array();
		if($is_load){ $this->load(); }
	}

	public function reload(){
		$this->tweets = array();
		$this->load();
	}

	public function all(){
		return $this->tweets;
	}

	public function unregistered_tweets(){
		$unregistered_tweets = array_map(
			function($tweet){
				return $tweet->is_registed() ? null : $tweet;
			},
			$this->tweets
		);
		return array_values(array_filter($unregistered_tweets, function($tweet){ return isset($tweet); }));
	}

	public function add_tweet($tweet){
		// $tweet は twitteroauth で取得してきたつぶやきオブジェクト
		if($this->is_registered_tweet((int)$tweet->id)) return false; // すでに登録されているものは無視

		$this->tweets[] = Tweet::build($tweet);
		return true;
	}

	public function save(){
		// add_tweet で追加したツイートを保存
		$tweets = array();
		foreach($this->unregistered_tweets() as $tweet){
			$tweets[] = array(
				'tweet_id' => $tweet->tweet_id,
				'text' => $tweet->text,
				'created_at' => date('Y-m-d H:i:s', $tweet->created_at),
				'registration_at' => date('Y-m-d H:i:s')
			);
		}
		if(TweetBlogDB::insert('tweets', $tweets) == 0){ return []; }
		$tweet_ids = TweetBlogDB::select(
			'tweets',
			'tweet_id IN :tweet_ids',
			array('tweet_ids' => array_map(function($tweet){ return $tweet['tweet_id']; }, $tweets)),
			['id', 'tweet_id'],
			null,
			null
		);

		foreach($this->unregistered_tweets() as $tweet){
			$tweet->set_id($tweet_ids);
			$tweet->tag_register();
			$tweet->pic_register();
		}

		$this->registered_max_tweet_id = null; // 情報をDBに追加したのでキャッシュしておいた最大値を削除
	}

	public function registered_max_tweet_id(){
		if(is_null($this->registered_max_tweet_id)){
			$result = TweetBlogDB::select('tweets', null, null, array('max(tweet_id) as max_id'));
			$this->registered_max_tweet_id = $result[0]['max_id'] ? (int)$result[0]['max_id'] : 0;
		}
		return $this->registered_max_tweet_id;
	}

	protected function is_registered_tweet($tweet_id){
		return $tweet_id <= $this->registered_max_tweet_id();
	}

	protected function load(){
		foreach(TweetBlogDB::select("tweets", null, null, ['*'], ['registration_at', 'DESC']) as $tweet_data){
			$this->tweets[] = Tweet::build($tweet_data);
		}
	}
}
