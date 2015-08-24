<?php
require_once dirname(__FILE__)."/MySQLAdapter.php";

class DBRow{
	protected $__db_values;
	private static $__db_column_names = array();
	private static $__db_column_types = array();
	private static $__db_table_name   = array();

	public static function find($id = null){
		if(empty($id)){ return false; }

		$column_values = TweetBlogDB::find(self::get_table_name(), $id);
		if(empty($column_values)) { return false; }
		$column_obj = new static();
		$column_obj->set_values($column_values);
		return $column_obj;
	}

	public static function set_table_name($table_name){
		// 関連するテーブル名をセットする。
		// その際、合わせてカラム名とカラムのタイプも取得
		self::__set_table_name($table_name, get_called_class());

		$db = MySQLAdapter::get_instance();
		if(empty($db)) { return; }

		$columns = $db->desc($table_name);
		if(empty($columns)) { return; }

		$column_names = array();
		$column_types = array();

		foreach($columns as $col_info){
			$column_names[]                   = $col_info['Field'];
			$column_types[$col_info['Field']] = preg_replace('/\([0-9]+\)/', '', $col_info['Type']);
		}

		self::__set_column_names($column_names, get_called_class());
		self::__set_column_types($column_types, get_called_class());
	}

	private static function __set_table_name($table_name, $class_name){
		self::$__db_table_name[$class_name] = $table_name;
	}

	private static function __set_column_names($table_name, $class_name){
		self::$__db_column_names[$class_name] = $table_name;
	}

	private static function __set_column_types($table_name, $class_name){
		self::$__db_column_types[$class_name] = $table_name;
	}

	protected static function get_column_names(){
		return self::__get_column_names(get_called_class());
	}

	private static function __get_column_names($class_name){
		return self::$__db_column_names[$class_name];
	}

	protected static function get_column_types(){
		return self::__get_column_types(get_called_class());
	}

	private static function __get_column_types($class_name){
		return self::$__db_column_types[$class_name];
	}

	protected static function get_table_name(){
		return self::__get_table_name(get_called_class());
	}

	private static function __get_table_name($class_name){
		return self::$__db_table_name[$class_name];
	}

	public function __construct(){
		foreach(self::get_column_names() as $column_name){ $this->__db_values[$column_name] = null; }
	}

	public function __get($name){
		if(array_key_exists($name, $this->__db_values)){
			return $this->__db_values[$name];
		}elseif($name == 'table_name'){
			return self::get_table_name();
		}else{
			$e = new Exception;
			$error = array_shift($e->getTrace());
			trigger_error('Undefined property: ' . get_class($this) . '::$' . $name . ' in ' . $error['file'] . ' on line ' . $error['line']);
		}
	}

	public function save(){
		// Update メソッドを実装したら作る
		return true;
	}

	protected function set_values($array){
		foreach($this->__db_values as $key => $_value){
			if(!array_key_exists($key, $array) || is_null($array[$key])) { continue; }
			switch(self::get_column_types()[$key]){
				case 'bigint' :
				case 'int' :
					$this->__db_values[$key] = (int)$array[$key];
					break;
				case 'timestamp' :
					$this->__db_values[$key] = strtotime($array[$key]);
					break;
				default :
					$this->__db_values[$key] = $array[$key];
			}
		}
	}
}
