<?php
namespace Glorea\TweetBlog;

class TweetDate extends \Model {
    public static $_table = 'tweet_dates';

    public static function register($date){
        if(self::where('tweet_date', $date)->find_one()) return;
        self::create(['tweet_date' => $date])->save();
    }
}
