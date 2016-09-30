<?php
namespace Glorea\TweetBlog;

class Config {
    private static $config = null;
    protected $values = null;

    public function values(){
        return $this->values;
    }

    protected function __construct($filepath){
        $this->json_decode($filepath);
    }

    protected function json_decode($filepath) {
        $this->values = json_decode(file_get_contents($filepath));
    }

    public static function database(){
        return self::config()->values()->database;
    }

    public static function site(){
        return self::config()->values()->site;
    }

    public static function twitter(){
        return self::config()->values()->twitter;
    }

    public static function load($filepath = null){
        if(is_null($filepath)) $filepath = 'config/application.json';
        self::$config = new self($filepath);
    }

    protected static function config(){
        if(is_null(self::$config)) self::load();
        return self::$config;
    }
}
