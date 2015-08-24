<?php
require_once dirname(__FILE__)."/lib/DBRow.php";
require_once dirname(__FILE__)."/TweetTags.php";

class TweetPic extends DBRow{
	public static function create($tweet_id, $url, $tags = array()){
		if(empty($tweet_id) || empty($url)) { return false; }

		$pic_obj = new static();
		$pic_obj->register($tweet_id, $url, $tags);
		return $pic_obj;
	}

	public static function build($values){
		$pic_obj = new static();
		$pic_obj->set_pic_values($values);
		return $pic_obj;
	}

	public function set_pic_values($values){
		$this->set_values($values);
	}

	public function register($tweet_id, $url, $tags = null){
		if(empty($tweet_id) || empty($url)) { return false; }
		if(empty($tags)) { $tags = new TweetTags(); }  // ダミー

		$filename = basename($url);
		if(!$this->download($url, $filename)) { return false; }

		if(TweetBlogDB::insert('pics', [array('filename' => $filename, 'alt' => $tags->to_s(), 'tweet_id' => $tweet_id)]) == 1){
			$pics = TweetBlogDB::select("pics", 'filename = :filename', array('filename' => $filename));
			$this->set_values($pics[0]);
		}

		foreach($tags->all() as $tag){
			if(empty(TweetBlogDB::select('pics_hashtags', 'pic_id = :pic_id AND hashtag_id = :hashtag_id', array('pic_id' => $this->id, 'hashtag_id' => $tag->id)))){
				TweetBlogDB::insert('pics_hashtags', [array('pic_id' => $this->id, 'hashtag_id' => $tag->id)]);
			}
		}
	}

	protected function download($url, $filename = null, $dir_path = null){
		if(empty($url)) { return false; }
		if(empty($dir_path)) { $dir_path = TweetBlogConfig::media_dir(); }
		if(empty($filename)) { $filename = basename($url); }
		if(!$this->dir_check($dir_path)) { return false; }

		return file_put_contents($dir_path . $filename, file_get_contents($url));
	}

	protected function dir_check($dir_path = null, $is_force = true){
		if(empty($dir_path)) { $dir_path = TweetBlogConfig::media_dir(); }
		if(file_exists($dir_path)){ return true; }

		if(!$is_force){ return false; }
		mkdir($dir_path, 755, true);

		return true;
	}
}
TweetPic::set_table_name('pics');
