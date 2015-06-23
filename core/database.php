<?php
	
	
class DB {
	
	public $mySql;
	protected $result;
	public $prefix;
	
	public function __construct() {
		$this->init();
	}
	
	private function init() {
		$mysqli = new mysqli(DB_SERVER . (DB_PORT ? ':' . DB_PORT : ''), DB_USER, DB_PASS, DB_NAME);
		
		$this->prefix = DB_PREFIX;
		
		if ($mysqli->connect_errno) {
			echo 'Database Error: ' . $mysqli->connect_error;
		} else {
			$this->mySql = $mysqli;
		}
	}
	
	public function get($from, $limit = 0, $offset = 0) {
		$from = $this->prefix . $from;
		$limit = $limit ? "LIMIT $limit" : "";
		$offset = $offset ? "OFFSET $offset" : "";
		$statement = "SELECT * FROM $from $limit $offset";
		$this->result = $this->mySql->query($statement);
		return new DB_Resultset($this->result);
	}
	
	/**
	 * get_where function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $from
	 * @param mixed $where
	 * @param int $limit (default: 0)
	 * @param int $offset (default: 0)
	 * @return void
	 */
	public function get_where($from, $where, $limit = 0, $offset = 0) {
		$from = $this->prefix . $from;
		if (is_array($where)) {
			$where_array = [];
			foreach ($where as $k=>$v) {
				$where_array[] = "$k='$v'";
			}
			$where = implode(' AND ', $where_array);
		}
		$limit = $limit ? "LIMIT $limit" : "";
		$offset = $offset ? "OFFSET $offset" : "";
		$statement = "SELECT * FROM $from where $where $limit $offset";
		$result = $this->mySql->query($statement);
		$this->result = $result;
		return new DB_Resultset($result);
	}
	
	/**
	 * insert function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $table
	 * @return void
	 */
	public function insert($table, $data) {
		$table = $this->prefix . $table;
		$values = [];
		$columns = [];
		foreach ($data as $column=>$value) {
			$values[] = "'$value'";
			$columns[] = $column;
		}
		$data_string = implode(',', $values);
		$column_string = implode(',', $columns);
		$statement = "insert into $table ($column_string) values($data_string)";
		return $this->mySql->query($statement);
	}
	
	public function update($table, $data, $where = false) {
		$table = $this->prefix . $table;
		$values = [];
		unset($data['id']);
		$where = $where ? 'where ' . $this->_parse_where($where) : '';
		foreach ($data as $column=>$value) {
			$values[] = "$column = '$value'";
		}
		$data_string = implode(',', $values);
		$statement = "update $table set $data_string $where";
		return $this->mySql->query($statement);
	}
	
	public function delete($table, $where = false) {
		if (!$where) return false;
		$table = $this->prefix . $table;
		$where = $where ? 'where ' . $this->_parse_where($where) : '';
		$statement = "delete from $table $where";
		return $this->mySql->query($statement);
	}
	
	private function _parse_where($where) {
		if (is_string($where)) return $where;
		$output = [];
		foreach ($where as $key=>$value) {
			$output[] = "$key = '$value'";
		}
		return implode(' AND ', $output);
	}
	
}

class DB_Resultset {
	
	public $raw;
	public $rows;
	public $num_rows;
	
	public function __construct($result) {
		$this->raw = $result;
		while($row = $result->fetch_array(MYSQLI_ASSOC)) {
			$this->rows[] = new DB_Row($row);
		}
		$this->num_rows = $result->num_rows;
	}
}

class DB_Row {
	public $columns = [];
	public $size = 0;
	public $row_array = [];
	
	public function __construct(array $row = array()) {
		foreach($row as $name=>$value) {
			$this->{$name} = $value;
			$this->columns[] = $name;
			$this->row_array[$name] = $value;
		}
		$this->size = count($row);
	}
}