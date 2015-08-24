<?php
require_once dirname(__FILE__)."/TweetTag.php";

class TweetTags{
	protected $tags;
	protected $tweet_id;

	public function __construct($tweet_id = null){
		$this->tags     = array();
		$this->tweet_id = $tweet_id;

		if(!empty($tweet_id)){ $this->load(); }
	}

	public function register($tweet_obj, $tweet_id = null){
		if(!empty($tweet_id)) { $this->tweet_id = $tweet_id; }
		if(empty($this->tweet_id) || empty($tweet_obj)) { return; }

		foreach($tweet_obj->entities->hashtags as $hashtag){
			$this->tags[] = TweetTag::create($this->tweet_id, $hashtag->text);
		}
	}

	public function all(){
		return $this->tags;
	}

	public function to_s(){
		$tag_strs = array();
		foreach($this->tags as $tag){
			$tag_strs[] = $tag->text;
		}
		return join(' ', $tag_strs);
	}

	protected function load(){
		foreach(
			TweetBlogDB::select(
				"tweets AS t LEFT OUTER JOIN tweets_hashtags AS th ON t.id = th.tweet_id LEFT OUTER JOIN hashtags AS h ON th.hashtag_id = h.id",
				't.id = :tweet_id',
				array('tweet_id' => $this->tweet_id),
				['h.*'],
				'h.id'
			) as $tag_data
		){
			if(empty($tag_data['id'])) { continue; }
			$this->tags[] = TweetTag::build($this->tweet_id, $tag_data);
		}
	}
}
