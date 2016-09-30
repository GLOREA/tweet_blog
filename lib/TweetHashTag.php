<?php
namespace Glorea\TweetBlog;

class TweetHashTag extends \Model {
    public static $_table = 'tweet_hash_tags';

    public static function build_tags($data){
    }

    public static function create($data) {
        // stdClass の場合は json データとして扱う
        $hash_tag = parent::create();

        if($data instanceof \stdClass){
            $media->download($data->media_url);
        }else{
            $media->set($data);
        }
        $hash_tag->registration_at = time();

        return $hash_tag;
    }
}
