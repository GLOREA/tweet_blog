<?php
namespace Glorea\TweetBlog;
require_once __DIR__ . "/idiorm/idiorm.php";
require_once __DIR__ . "/paris/paris.php";

\ORM::configure(
  Config::database()->adapter .
  ':dbname=' . Config::database()->schema .
  ';host=' . Config::database()->host .
  ';charset=utf8'
);
\ORM::configure('username', Config::database()->username);
\ORM::configure('password', Config::database()->password);

class Initializer {
    public function __construct(){
        $this->db = \ORM::get_db();
    }

    public function run($is_force = false){
        // $this->create_table('hashtags', array('tag text'), $is_force);
        $this->create_table(
            'tweet_medias',
            array(
                'filename' => array('type' => 'text', 'null' => false),
                'alt' => array('type' => 'text'),
                'tweet_id' => array('type' => 'int', 'null' => false, 'index' => true),
                'registration_at' => array('type' => 'int', 'null' => false)
            ), $is_force
        );

        $this->create_table(
            'tweets',
            array(
                'tweet_id' => array('type' => 'bigint', 'null' => false),
                'text' => array('type' => 'text', 'null' => false),
                'created_at' => array('type' => 'int', 'null' => false, 'index' => true),
                'registration_at' => array('type' => 'int', 'null' => false)
            ), $is_force
        );

        $this->create_table(
            'tweet_dates',
            array(
                'tweet_date' => array('type' => 'int', 'null' => false, 'index' => true, 'unique' => true),
            ), $is_force
        );

        $this->create_table(
            'tweets_tags',
            array(
                'tweet_id' => array('type' => 'int', 'null' => false, 'index' => true),
                'tag_id' => array('type' => 'int', 'null' => false, 'index' => true)
            ), $is_force
        );

        $this->create_table(
            'tags',
            array(
                'tag' => array('type' => 'text', 'null' => false)
            ), $is_force
        );

        $this->create_table(
            'tweets_tweet_hash_tags',
            array(
                'tweet_id' => array('type' => 'int', 'null' => false, 'index' => true),
                'tweet_hash_tag_id' => array('type' => 'int', 'null' => false, 'index' => true)
            ), $is_force
        );

        $this->create_table(
            'tweet_hash_tags',
            array(
                'tag' => array('type' => 'text', 'null' => false)
            ), $is_force
        );
        // $this->create_table('pics_hashtags', array('pic_id int, hashtag_id int'), $is_force);
    }

    protected function execute($sql, $__place_holder = null){
        if(is_array($__place_holder)){
            $place_holder = array();
            foreach($__place_holder as $key => $value){
                if(!is_array($value)){
                    // データが配列でない場合は、素直に情報を詰めて終わる
                    $place_holder[] = $this->get_place_holder_info($key, $value);
                    continue;
                }

                $array_count = 0;
                $keys = array();
                foreach($value as $array_value){
                    $key_buf = $key . '_' . $array_count;
                    $keys[] = ':' . $key_buf;
                    $place_holder[] = $this->get_place_holder_info($key_buf, $array_value);
                    $array_count++;
                }
                $sql = preg_replace(
                    '/:' . $key . '([\r\n\t; =]|$)/',
                    '(' . join(',', array_map(function($k){ return $k; }, $keys)) . ')\1',
                    $sql
                );
            }
        }

        $stmt = $this->db->prepare($sql);
        if(!$stmt){
            var_dump($sql); // HACK: log に吐くようにする
            trigger_error('クエリ作成時にトラブった');
        }
        if(isset($place_holder)){
            foreach($place_holder as $info){ $stmt->bindValue($info['key'], $info['value'], $info['type']); }
        }
        $stmt->execute();
        return $stmt;
    }

    protected function create_table($table_name, $columns, $is_force = false){
        if(!$is_force && $this->is_there_table($table_name)) return false;
        $this->drop($table_name);
        $index_columns = array();
        $column_queries = array();
        foreach($columns as $column_name => $column_params){
            if(isset($column_params['index']) && $column_params['index']) $index_columns[] = $column_name;
            $column_queries[] = $column_name . ' ' .
                                $column_params['type'] .
                                ((!isset($column_params['null']) || $column_params['null']) ? '' : ' NOT NULL') .
                                ((isset($column_params['unique']) && $column_params['unique']) ? ' UNIQUE' : '') .
                                ((isset($column_params['default']) && $column_params['default']) ? ' DEFAULT' . $column_params['default'] : '');
        }

        return $this->execute(
            'CREATE TABLE ' . $table_name .
            ' (`id` int(11) NOT NULL AUTO_INCREMENT,' . 
            join(',', $column_queries) . ',' .
            (count($index_columns) > 0 ? 'INDEX(`' . join('`, `', $index_columns) . '`),' : '') .
            'PRIMARY KEY (`id`) )' .
            'ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
    }

    protected function drop($table_name){
        return $this->execute('DROP TABLE IF EXISTS `' . $table_name . '`;');
    }

    protected function desc($table_name){
        return $this->execute('SHOW TABLES FROM `' . Config::database()->schema . '` LIKE \'' . $table_name . '\';')->fetchAll();
        // return $this->execute('DESCRIBE `' . $table_name . '`;')->fetchAll();
    }

    protected function is_there_table($table_name){
        $result = $this->desc($table_name);
        return !empty($result);
    }
}
