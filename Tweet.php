<?php
require_once dirname(__FILE__)."/lib/DBRow.php";
require_once dirname(__FILE__)."/TweetTags.php";
require_once dirname(__FILE__)."/TweetPics.php";

class Tweet extends DBRow{
	protected $tweet_obj;
	protected $tags;
	protected $pics;
	protected $is_registed;
	protected $tweet_analyzer;

	public static function build($tweet){
		$tweet_values = array();
		$tweet_obj = new static();

		$tweet_obj->set_datas($tweet);
		return $tweet_obj;
	}

	public function set_datas($tweet){
		// 配列だった場合はDBから取得したものとしてあつかう
		if(is_array($tweet)){
			$this->is_registed = true;
			$tweet_values = $tweet;
		}else{
			$this->is_registed = false;
			$tweet_values = (array) $tweet;
			$tweet_values['tweet_id'] = $tweet_values['id'];
			unset($tweet_values['id']);
			$this->tweet_obj = $tweet;
		}
		$this->set_values($tweet_values);

		$this->tags = new TweetTags($this->id);
		$this->pics = new TweetPics($this->id);
	}

	public function is_registed(){
		return $this->is_registed;
	}

	public function set_id($tweet_ids){
		$id = $this->tweet_id; // __get を使ってるせいか、代入しないと empty が正常動作しないため
		if(empty($id)) { return false; }
		foreach($tweet_ids as $tweet_id){
			if($this->tweet_id != (int)$tweet_id['tweet_id']) { continue; }
			$this->__db_values['id'] = (int)$tweet_id['id'];
			$this->is_registed = true; // DBに登録されていたので登録済みフラグを建てる
			break;
		}
	}

	public function tag_register(){
		if(!$this->is_hashtags()) { return; }
		$this->tags->register($this->tweet_obj, $this->id);
	}

	public function pic_register(){
		if(!$this->is_media()) { return; }
		$this->pics->register($this->tweet_obj, $this->id, $this->tags);
	}

	public function is_entities(){
		return !empty($this->tweet_obj) && array_key_exists('entities', (array) $this->tweet_obj);
	}

	public function is_media(){
		return $this->is_entities() ? array_key_exists('media', (array) $this->tweet_obj->entities) : false;
	}

	public function is_hashtags(){
		return $this->is_entities() ? array_key_exists('hashtags', (array) $this->tweet_obj->entities) : false;
	}
}
Tweet::set_table_name('tweets');
