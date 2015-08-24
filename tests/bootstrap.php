<?php
// 定数
require_once dirname(__FILE__) . '/../TweetBlogConfig.php';

function __autoload($class_name) {
	$autoload = function($class_name, $dir_name) use(&$autoload){
		$file_fullpath = $dir_name . '/' . $class_name . '.php';
		if(file_exists($file_fullpath)){
			require_once $file_fullpath;
			return true;
		}
		$files = scandir($dir_name);
		foreach($files as $filename){
			if(!strcmp($filename, '..') || !strcmp($filename, '.')){ continue; }
			$file_fullpath = $dir_name . '/' . $filename;
			if(!is_dir($file_fullpath)){ continue; }
			if($autoload($class_name, $file_fullpath)){ return true; }
		}
		return false;
	};

	$autoload($class_name, dirname(__FILE__) . '/..');
}
