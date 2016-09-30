<?php
namespace Glorea\TweetBlog;

require_once dirname(__FILE__)."/twitteroauth/autoload.php";

class TweetAnalyzer {
    protected $twitter;

    protected $consumer_key;
    protected $consumer_secret;
    protected $oauth_callback;
    protected $twitter_access_token;
    protected $screen_name;

    protected $access_token;

    public function __construct($init_access_token = false){
        $this->consumer_key = Config::twitter()->consumer_key;
        $this->consumer_secret = Config::twitter()->consumer_secret;
        $this->oauth_callback = 'http' . (empty($_SERVER["HTTPS"]) ? '' : 's') . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        $this->twitter_access_token = Config::twitter()->accesstoken_file;
        $this->screen_name = Config::twitter()->screen_name;

        // アクセストークンが取得できない場合は、Twitter の認証ページにリダイレクトされるので初期化をここで終わらせる
        if(!$this->initialize_access_token($init_access_token)) return false;

        $this->twitter = $this->generate_twitter_instance($this->access_token);

        $this->registered_max_tweet_id = null;
    }

    public function register($all_regist = false){
        return $this->tweet_register($all_regist);
    }

    protected function tweet_register($all_regist = false){
        if(! $this->is_twitter_connect()) return false;
        try{
            $params = array();
            if($all_regist){
                $tweets = array();
                for($i = 0; $i < 15; $i++){ // 3000件までしか担保されてないため、200 * 15 = 3000 で無限ループ防止
                    $tweets_buf = $this->get_tweets($params);
                    if(empty($tweets_buf)) { break; }
                    $params['max_id'] = end($tweets_buf)->id;
                    reset($tweets_buf);
                    $tweets = array_merge($tweets, Tweet::build_tweets($tweets_buf));
                }
            }else{
                $since_id = Tweet::registered_max_tweet_id();
                if(isset($since_id) && $since_id > 0) { $params['since_id'] = $since_id; }
                $tweets = Tweet::build_tweets($this->get_tweets($params));
            }

            return array_map(function($tweet){
                if($tweet->is_reply()) return null; // リプライツイートは無視する
                return $tweet->save();
            }, $tweets);
        }catch(Exception $e){
            // HACK: エラー処理
            trigger_error('Tweet 取得ミスった');
        }
    }

    public function is_twitter_connect(){
        return ! is_null($this->twitter);
    }

    public function get_tweets($params = array()){
        $params = array_merge(
            array(
                'screen_name' => $this->screen_name,
                'count' => 200,
                'exclude_replies' => true
            ), $params
        );
        $result = $this->twitter->get('statuses/user_timeline', $params);
        if(isset($result->errors)){
            trigger_error($result->errors->message);
            return null;
        }
        return $result;
    }

    protected function initialize_access_token($init_access_token){
        $this->save_access_token();
        $this->access_token = $this->load_access_token();
        if(!$this->access_token){ 
            if($init_access_token) $this->get_access_token();
            return false;
        }
        return true;
    }

    protected function save_access_token(){
        if(!array_key_exists('oauth_token', $_REQUEST) || is_null($_REQUEST['oauth_token'])) return false;

        $twitter = $this->generate_twitter_instance();
        $access_token = $twitter->oauth("oauth/access_token", $_REQUEST);
        return file_put_contents($this->twitter_access_token, $access_token['oauth_token'] . '<>' .$access_token['oauth_token_secret'], LOCK_EX);
    }

    protected function load_access_token(){
        if(!file_exists($this->twitter_access_token)) return false;

        $acess_token = explode('<>', file_get_contents($this->twitter_access_token));
        return array('access_token' => $acess_token[0], 'access_token_secret' => $acess_token[1]);
    }

    protected function get_access_token(){
        $twitter = $this->generate_twitter_instance();

        $request_token = $twitter->oauth("oauth/request_token", array("oauth_callback" => $this->oauth_callback));
        $url = $twitter->url("oauth/authorize", $request_token);

        header('Location: ' . $url);
    }

    protected function generate_twitter_instance($access_token = null){
        if(is_null($access_token)){
            return new \Abraham\TwitterOAuth\TwitterOAuth(
                            $this->consumer_key,
                            $this->consumer_secret
                        );
        }
        return new \Abraham\TwitterOAuth\TwitterOAuth(
                        $this->consumer_key,
                        $this->consumer_secret,
                        $access_token['access_token'],
                        $access_token['access_token_secret']
                    );
    }
}
