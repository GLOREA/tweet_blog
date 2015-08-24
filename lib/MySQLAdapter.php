<?php
class MySQLAdapter{
	private static $self;

	// PDO を適当にラップ
	protected $host;
	protected $scheme;
	protected $user;
	protected $pass;

	protected $handler;

	public static function get_instance(){
		// 外部から初期化されている場合のみ、自身のインスタンスを返す
		// HACK:DBRow で必要になったので実装したが、依存関係がむちゃくちゃなので整理必須
		if(isset(self::$self)) { return self::$self; }
		return null;
	}

	public function __construct($host, $scheme, $user, $pass){
		$this->host = $host;
		$this->scheme = $scheme;
		$this->user = $user;
		$this->pass = $pass;

		$this->handler = new PDO(
			$this->dns(),
			$this->user,
			$this->pass
		);

		self::$self = $this;
	}

	public function get_columns($table_name){
		$result = $this->desc($table_name);
		if(empty($result)) { return false; }
		return array_map(function($col_info){ return $col_info['Field']; }, $result);
	}

	public function desc($table_name){
		return $this->execute('DESCRIBE `' . $table_name . '`;')->fetchAll();
	}

	public function is_there_table($table_name){
		$result = $this->desc($table_name);
		return !empty($result);
	}

	public function insert($table_name, $datas){
		if(empty($datas)){ return false; }
		$keys = $this->hash_to_columnnames($datas);
		$values = array();
		foreach($datas as $data){
			$value = array();
			foreach($keys as $key){
				$value[] = isset($data[$key]) ? $this->handler->quote($data[$key]) : '';
			}
			$values[] = $value;
		}

		$sql = 'INSERT INTO ' . $table_name . ' (' . join(',', $keys) . ') VALUES ';
		$value_lines = array();
		foreach($values as $value){
			$value_lines[] = '(' . join(',', $value) . ')';
		}
		$sql .= join(',', $value_lines) . ';';

		return $this->execute($sql)->rowCount();
	}

	public function select($table_name, $where = null, $place_holder = null, $columns = ['*'], $order = null, $limit = 100, $offset = 0){
		$sql = 'SELECT ' . join(',', $columns) . ' FROM ' . $table_name;
		if(!empty($where)) { $sql .= ' WHERE ' . $where; }
		if(!empty($order)) {
			if(is_array($order)){
				$sql .= ' ORDER BY ' . join(' ', $order);
			}else{
				$sql .= ' ORDER BY ' . $order . ' ASC';
			}
		}
		if(!empty($limit)) { $sql .= ' LIMIT ' . $limit; }
		if(!empty($offset)) { $sql .= ' OFFSET ' . $offset; }
		return $this->execute($sql, $place_holder)->fetchAll();
	}

	public function create($table_name, $columns, $is_force = false){
		if(!$is_force && $this->is_there_table($table_name)) return false;
		$this->drop($table_name);
		return $this->execute(
			'CREATE TABLE ' . $table_name .
			' (`id` int(11) NOT NULL AUTO_INCREMENT,' . 
			join(',', $columns) . ',' .
			'PRIMARY KEY (`id`) )' .
			'ENGINE=InnoDB DEFAULT CHARSET=utf8;'
		);
	}

	protected function hash_to_columnnames($hash){
		$keys = array();
		foreach($hash as $data){
			$keys = array_merge($keys, array_keys($data));
		}
		return array_unique($keys);
	}

	protected function drop($table_name){
		return $this->execute('DROP TABLE IF EXISTS `' . $table_name . '`;');
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
		$stmt = $this->handler->prepare($sql);
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

	protected function get_place_holder_info($key, $value){
		switch(gettype($value)){
			case 'boolean' :
				$type = PDO::PARAM_BOOL;
				break;
			case 'integer' :
				if($value > 2147483647){
					$type = PDO::PARAM_STR;
				}else{
					$type = PDO::PARAM_INT;
				}
				break;
			default :
				$type = PDO::PARAM_STR;
				break;
		}
		return array('key' => ":$key", 'value' => $value, 'type' => $type);
	}

	protected function dns(){
		return 'mysql:host=' . $this->host . ';dbname=' . $this->scheme . ';charset=utf8';
	}

}
