<?php
require_once dirname(__FILE__)."/TweetBlogDB.php";

class TweetViewer{
	public $db;

	public function __construct(){
		$this->db = new TweetBlogDB();
	}

	public function tweets($where = null){
		return $this->db->select("tweets", $where, ['*'], ['registration_at', 'DESC']);
	}

	public function pics(){
		
	}
}