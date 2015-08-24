<?php
require_once dirname(__FILE__)."/lib/DBRow.php";

class TweetTag extends DBRow{
	protected $tweet_id;

	public static function create($tweet_id, $tag){
		if(empty($tweet_id) || empty($tag)) { return false; }

		$tag_obj = new static();
		$tag_obj->register($tweet_id, $tag);
		return $tag_obj;
	}

	public static function build($tweet_id, $values){
		$tag_obj = new static();
		$tag_obj->set_tag_values($tweet_id, $values);
		return $tag_obj;
	}

	public function set_tag_values($tweet_id, $values){
		$this->tweet_id = $tweet_id;
		$this->set_values($values);
	}

	public function tweet_id(){ $this->tweet_id; }

	public function register($tweet_id, $tag){
		if(empty($tweet_id) || empty($tag)) { return false; }

		$this->tweet_id = $tweet_id;
		$tags = TweetBlogDB::select('hashtags', 'tag = :hashtag', array('hashtag' => $tag));
		if(!empty($tags)){
			$this->set_values($tags[0]);
		}else{
			if(TweetBlogDB::insert('hashtags', [array('tag' => $tag)]) == 1){
				$tags = TweetBlogDB::select("hashtags", 'tag = :hashtag', array('hashtag' => $tag));
				$this->set_values($tags[0]);
			}
		}

		if(empty(TweetBlogDB::select('tweets_hashtags', 'tweet_id = :tweet_id AND hashtag_id = :hashtag_id', array('tweet_id' => $this->tweet_id, 'hashtag_id' => $this->id)))){
			TweetBlogDB::insert('tweets_hashtags', [array('tweet_id' => $this->tweet_id, 'hashtag_id' => $this->id)]);
		}
	}
}
TweetTag::set_table_name('hashtags');
