<?php
require_once dirname(__FILE__)."/TweetBlogConfig.php";
require_once dirname(__FILE__)."/lib/MySQLAdapter.php";

class TweetBlogDB {
	// DBAdapter を内包。面倒なことは大体こいつに押しこむ
	//  - DBの初期化とか
	protected static $db = null;

	public static function table_setup($is_force = false){
		self::__init_db();
		// 初期化を全部ここに書く
		// - is_force : true の場合はテーブル削除後に作成
		self::$db->create('hashtags', array('tag text'), $is_force);
		self::$db->create('pics', array('filename text', 'alt text', 'tweet_id int', 'registration_at TIMESTAMP'), $is_force);
		self::$db->create('tweets', array('tweet_id bigint', 'text text', 'created_at TIMESTAMP', 'registration_at TIMESTAMP'), $is_force);
		self::$db->create('tweets_hashtags', array('tweet_id int, hashtag_id int'), $is_force);
		self::$db->create('pics_hashtags', array('pic_id int, hashtag_id int'), $is_force);
	}

	public static function is_there_table($table_name){
		self::__init_db();
		return self::$db->is_there_table($table_name);
	}

	public static function insert($table_name, $datas){
		self::__init_db();
		return self::$db->insert($table_name, $datas);
	}

	public static function find($table_name, $id){
		$result = self::select($table_name, "$table_name.id = :id", array('id' => $id));
		if(empty($result)) { return null; }
		return $result[0];
	}

	public static function select($table_name, $where = null, $place_holder = null, $columns = ['*'], $order = null, $limit = 100, $offset = 0){
		self::__init_db();
		return self::$db->select($table_name, $where, $place_holder, $columns, $order, $limit, $offset);
	}

	public static function __init_db(){
		if(!empty(self::$db)) { return self::$db; }
		self::$db = new MySQLAdapter(
			TweetBlogConfig::db_host(),
			TweetBlogConfig::db_scheme(),
			TweetBlogConfig::db_user(),
			TweetBlogConfig::db_pass()
		);
		return self::$db;
	}
}
TweetBlogDB::__init_db();
