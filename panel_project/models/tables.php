<?php

class Table {
	public $table;
	public $rows = array();
	public $table_def = array();
	
	public function __construct($table, $fill = false) {
		$this->table = $table;
		$this->get_table_def();
		if ($fill) $this->fillTable();
	}
	
	public function createWithFields($fields, $data = NULL) {
		$this->db_add_table($this->table, $fields, $data);
	}
	
	private function db_add_table($name, $columns, $data=NULL) {
		$cols = array();
		foreach ($columns as $col) {
			$cols[] = $col['name'] . ' ' . $col['type'] . ' ' . $col['attr'];
		}
		$cols_s = implode(',', $cols);
		$sql = "CREATE TABLE $name ($cols_s)";
		global $mysqli;
		if ($mysqli) {
			$stmt = $mysqli->prepare($sql);
			try {
				$st = $stmt->execute();
			}
			catch (Exception $exception) {
				
			}
			$stmt->close();
			if ($st == 1 && $data) {
				$types = $data['types'];
				foreach ($data['rows'] as $row) {
					$r_cols = array();
					$r_vals = array();
					$r_parm = array();
					foreach ($row as $key => $value) {
						$r_cols[] = $key;
						$r_vals[] = $value;
						$r_parm[] = '?';
					}
					$r_cols = implode(',',$r_cols);
					$r_parm = implode(',',$r_parm);
					$stmt = $mysqli->prepare("INSERT INTO $name ($r_cols) values($r_parm)");
					$params=array_merge(
						array($types),$r_vals
					);
					call_user_func_array(array($stmt, 'bind_param'), refValues($params));
					$stmt->execute();
					$stmt->close();
				}
			}
		}
	}
	
	public function size() {
		return count($this->rows);
	}
	
	private function get_table_def() {
		global $mysqli;
		$stmt = $mysqli->stmt_init();
		$stmt = $mysqli->prepare("describe {$this->table}");
		$stmt->execute();
		$stmt->bind_result($field, $type, $null, $key, $default, $extra);
		$fields = array();
		$primary = "";
		while ($stmt->fetch()) {
			$row = array();
			$row['field'] = $field;
			$row['type'] = $type;
			$row['null'] = $null;
			$row['key'] = $key;
			$row['default'] = $default;
			$row['extra'] = $extra;
			$fields[$field] = $row;
		}
		$this->table_def = $fields;
		$stmt->close();
	}
	
	private function fieldString() {
		$fields = array();
		foreach ($this->table_def as $def) {
			$fields[] = $def['field'];
		}
		return implode(', ', $fields);
	}
	
	private function fields() {
		$fields = array();
		foreach ($this->table_def as $def) {
			$fields[] = $def['field'];
		}
		return $fields;
	}
	
	public function insertRow($cells) {
		
	}
	
	public function fillTable() {
		global $mysqli;
		$field_string = $this->fieldString();
		$stmt = $mysqli->prepare("select $field_string from {$this->table}");
		$stmt->execute();
		$params = array();
		$results = array();
		$fields = $this->fields();
		foreach ($fields as $field) {
			$params[] = '$'.$field;
		}
		$param_string = implode(', ', $params);
		eval('$stmt->bind_result(' . $param_string . ');');
		while($stmt->fetch()) {
			$row = new TableRow($this->table_def);
			foreach ($fields as $f) {
				eval('$value = $' . $f . ';');
				$cell = new TableCell($f, $this->table_def[$f]['type'], $value);
				$row->addCell($cell);
			}
			$this->rows[] = $row;
		}
		$stmt->close();
	}
}

class TableRow {
	public $columns = array();
	public $cells = array();
	
	public function __construct($columns) {
		$this->columns = $columns;
	}
	
	public function addCell(TableCell $cell) {
		$this->cells[$cell->name] = $cell;
	}
	
	public function size() {
		return count($this->columns);
	}
}

class TableCell {
	public $name;
	public $type;
	public $value;
	
	public function __construct($name, $type, $value) {
		$this->name = $name;
		$this->type = $type;
		$this->value = $value;
	}
	
}
