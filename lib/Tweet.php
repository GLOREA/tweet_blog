<?php
namespace Glorea\TweetBlog;

class Tweet extends \Model {
    public static $_table = 'tweets';
    protected static $_registered_max_tweet_id = null;
    protected $_medias = null;
    protected $_hash_tags = null;
    protected $_set_in_reply_to_status_id = null;

    public function medias(){ return $this->has_many('TweetMedia', 'tweet_id'); }
    // public function hashtags(){ return $this->has_many('TweetHawshTag', 'tweet'); }

    public function created_at_f(){
        return $this->created_at_datetime()->format(Config::twitter()->timestamp_format);
    }

    public function created_at_datetime(){
        $datetime = new \DateTime();
        return $datetime->setTimestamp($this->created_at);
    }

    public function date(){
        return $this->created_at_datetime()->format('Ymd');
    }

    public function is_reply(){
        return !is_null($this->_set_in_reply_to_status_id);
    }

    public function set_in_reply_to_status_id($in_reply_to_status_id){
        $this->_set_in_reply_to_status_id = $in_reply_to_status_id;
    }

    public function set_hash_tags($hash_tags){
        $this->_hash_tags = $hash_tags;
    }

    public function set_medias($medias){
        $this->_medias = $medias;
    }

    public function save(){
        \ORM::get_db()->beginTransaction();
        if($this->is_new()) Tweet::reset_in_reply_to_status_id();

        TweetDate::register($this->date());
        parent::save();

        if(is_array($this->_medias)){
            foreach($this->_medias as $media){
                $media->tweet_id = $this->id;
                $media->save();
            }
        }
        
        \ORM::get_db()->commit();
    }

    public static function fetch_tweets($from, $to = null){
        if(is_null($to)) $to = $from;
        if($from instanceof TweetDate) $from = $from->tweet_date;
        if($to instanceof TweetDate) $to = $to->tweet_date;
        $from_time = new \DateTime();
        $to_time = new \DateTime();
        $from_time->setDate(substr($from, 0, 4), substr($from, 4, 2), substr($from, 6, 2));
        $from_time->setTime(0, 0, 0);
        $to_time->setDate(substr($to, 0, 4), substr($to, 4, 2), substr($to, 6, 2));
        $to_time->setTime(23, 59, 59);
var_dump($from_time);
var_dump($to_time);
        return self::where_raw(
            '`created_at` BETWEEN ? AND ?',
            array($from_time->getTimestamp(), $to_time->getTimestamp())
        )->find_many();
    }

    public static function create($data) {
        // stdClass の場合は json データとして扱う
        $tweet = parent::create();

        if($data instanceof \stdClass){
            $tweet->set([
                'tweet_id' => $data->id,
                'text' => $data->text,
                'created_at' => strtotime($data->created_at)
            ]);

            if(property_exists($data, 'in_reply_to_status_id')) $tweet->set_in_reply_to_status_id($data->in_reply_to_status_id);
            if(count($data->entities->hashtags) > 0) $tweet->set_hash_tags(TweetHashTag::build_tags($data->entities->hashtags));
            if(property_exists($data->entities, 'media')) $tweet->set_medias(TweetMedia::build_medias($data->entities->media));
        }else{
            $tweet->set($data);
        }

        $tweet->registration_at = time();
        return $tweet;
    }

    public static function reset_in_reply_to_status_id(){
        self::$_registered_max_tweet_id = null;
    }

    public static function registered_max_tweet_id(){
        if(is_null(self::$_registered_max_tweet_id)){
            self::$_registered_max_tweet_id = \ORM::for_table(self::$_table)->max('tweet_id');
        }
        return self::$_registered_max_tweet_id;
    }

    public static function where_tweet_id_in($tweet_ids){
        return self::where_raw(
            '`tweet_id` IN (' . implode(',', array_fill(0, count($tweet_ids), '?')) . ')',
            $tweet_ids
        );
    }

    public static function build_tweets($tweet_datas) {
        $tweets = self::where_tweet_id_in(self::extract_tweet_ids($tweet_datas))->find_many();
        $has_tweet_ids = array_map(function($tweet){ return $tweet->tweet_id; }, $tweets);

        $build_tweets = [];
        foreach($tweet_datas as $tweet_data){
            if(in_array($tweet_data->id, $has_tweet_ids)) continue;
            $build_tweets[] = self::build($tweet_data);
        }
        return array_merge($build_tweets, $tweets);
    }
    
    public static function build($tweet_data){
        $tweet = self::create($tweet_data);
        return $tweet;
    }

    protected static function extract_tweet_ids($tweet_datas){
        // DBデータかjsonかで処理を振り分ける
        return static::extract_tweet_ids_for_json($tweet_datas);
    }

    protected static function extract_tweet_ids_for_json($tweet_jsons){
        return array_map(function($tweet_json){
            return $tweet_json->id;
        }, $tweet_jsons);
    }
}
