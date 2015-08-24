<?php
require_once dirname(__FILE__)."/TweetPic.php";

class TweetPics{
	protected $pics;
	protected $tweet_id;

	public function __construct($tweet_id = null){
		$this->pics     = array();
		$this->tweet_id = $tweet_id;

		if(!empty($tweet_id)){ $this->load(); }
	}

	public function register($tweet_obj, $tweet_id = null, $tags = array()){
		if(!empty($tweet_id)) { $this->tweet_id = $tweet_id; }
		if(empty($this->tweet_id) || empty($tweet_obj)) { return; }

		foreach($tweet_obj->entities->media as $media){
			if($media->type != 'photo') { continue; }	// 画像以外のメディアは無視する
			$this->pics[] = TweetPic::create($this->tweet_id, $media->media_url);
		}
	}

	protected function load(){
		foreach(TweetBlogDB::select("pics", 'tweet_id = :tweet_id', array('tweet_id' => $this->tweet_id)) as $pic_data){
			if(empty($pic_data['id'])) { continue; }
			$pic = TweetTag::build($this->tweet_id, $pic_data);
		}
	}
}
