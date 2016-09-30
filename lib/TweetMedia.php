<?php
namespace Glorea\TweetBlog;

class TweetMedia extends \Model {
    public static $_table = 'tweet_medias';
    public $tmp_file = null;

    public function save(){
        if(!is_null($this->tmp_file)){
            if(file_exists($this->tmp_file)){
                $this->filename = basename($this->tmp_file);
                rename($this->tmp_file, 'assets/medias/' . $this->filename);
            }
            $this->tmp_file = null;
        }
        parent::save();
    }

    public static function create($data) {
        // stdClass の場合は json データとして扱う
        $media = parent::create();

        if($data instanceof \stdClass){
            $media->download($data->media_url);
        }else{
            $media->set($data);
        }
        $media->registration_at = time();

        return $media;
    }

    public static function build_medias($media_datas) {
        // JSON データ作成以外で呼ばれることを想定していない
        $medias = array();

        foreach($media_datas as $media_data){
            if($media_data->type != 'photo') continue;  // 画像以外のメディアは無視する
            $medias[] = self::create($media_data);
        }

        return $medias;
    }

    protected function download($url, $filename = null, $dir_path = null){
        if(empty($url)) return false;
        if(empty($dir_path)) $dir_path = Config::twitter()->tmp_dir;
        if(empty($filename)) $filename = basename($url);
        if(!$this->dir_check($dir_path)) return false;

        $this->tmp_file = $dir_path . $filename;
        return file_put_contents($this->tmp_file, file_get_contents($url));
    }

    protected function dir_check($dir_path = null, $is_force = true){
        if(empty($dir_path)) $dir_path = Config::twitter()->tmp_dir;
        if(file_exists($dir_path)) return true;

        if(!$is_force) return false;
        mkdir($dir_path, 755, true);

        return true;
    }
}
