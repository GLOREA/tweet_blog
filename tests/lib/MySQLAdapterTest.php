<?php
class MySQLAdapterTest extends PHPUnit_Framework_TestCase {
	public function testConstruct(){
		try{
			new MySQLAdapter(
				TweetBlogConfig::db_host(),
				TweetBlogConfig::db_scheme(),
				TweetBlogConfig::db_user(),
				TweetBlogConfig::db_pass()
			);
			$this->assertTrue(true);
		}catch(Exception $e){
			$this->fail($e->getMessage());
		}

		try{
			new MySQLAdapter(
				TweetBlogConfig::db_host(),
				TweetBlogConfig::db_scheme(),
				TweetBlogConfig::db_user(),
				"tekito-"
			);
			$this->fail('例外発生なし');
		}catch(Exception $e){
			$this->assertEquals(0, $e->getCode());
		}
	}

	public function testIsThereTable(){
	}

	public function testInsert(){
	}

	public function testSelect(){
	}

	public function testCreate(){
	}
}
