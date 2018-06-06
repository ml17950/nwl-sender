<?php
class clsDB {
	var $host;
	var $dbuser;
	var $dbpass;
	var $dbname;
	var $link;
	
	var $result;
	var $last_errno;
	var $last_error;
	var $last_insert_id;
	var $duplicate_entry;
	var $num_rows;
	
	function __construct($host, $dbuser, $dbpass, $dbname) {
// 		if (ISDEV) echo "<!-- ",__CLASS__,"::",__FUNCTION__,"-->\n";
		
		$this->host = $host;
		$this->dbuser = $dbuser;
		$this->dbpass = $dbpass;
		$this->dbname = $dbname;
		
		$this->link = mysqli_connect($host, $dbuser, $dbpass, $dbname);
		if (!$this->link) {
    		die("Error ".mysqli_error($this->link));
		}
		
		mysqli_query($this->link, "SET NAMES '".DB_CHARSET."'"); 
		mysqli_query($this->link, "SET CHARACTER SET '".DB_CHARSET."'");
	}
	
	function __destruct() {
// 		if (ISDEV) echo "<!-- ",__CLASS__,"::",__FUNCTION__,"-->\n";
		
		mysqli_close($this->link);
	}
	
	function reset() {
// 		if (ISDEV) echo "<!-- ",__CLASS__,"::",__FUNCTION__,"-->\n";
		
		$this->last_insert_id = 0;
		$this->last_errno = 0;
		$this->last_error = '';
		$this->duplicate_entry = false;
		$this->num_rows = -1;
	}
	
	function query($sql) {
// 		if (ISDEV) echo "<!-- ",__CLASS__,"::",__FUNCTION__,"-->\n";
		
		$this->reset();
		
		$this->result = mysqli_query($this->link, $sql);
		
		if (!$this->result) {
			$this->last_errno = mysqli_errno($this->link);
			$this->last_error = mysqli_error($this->link);
			if ($this->last_errno == 1062)
				$this->duplicate_entry = true;
			if (ISDEV) echo "<!-- ",__CLASS__,"::",__FUNCTION__,":: result failed-->\n";
    		return false;
		}
		
		$this->last_insert_id = mysqli_insert_id($this->link);
		$this->num_rows = mysqli_affected_rows($this->link);
		
		//return true;
		return $this->result;
	}
	
	function fetch_assoc($dummy = false) {
		if (!$this->result)
			return false;
		return mysqli_fetch_assoc($this->result);
	}
	
	function fetch_array($dummy = false) {
		if (!$this->result)
			return false;
		return mysqli_fetch_array($this->result);
	}
	
	function query_assoc($sql) {
		$this->reset();
		
		$res = mysqli_query($this->link, $sql);
		
		if (!$res) {
			$this->last_errno = mysqli_errno($this->link);
			$this->last_error = mysqli_error($this->link);
			if ($this->last_errno == 1062)
				$this->duplicate_entry = true;
    		return false;
		}
		
		$this->num_rows = mysqli_affected_rows($this->link);
		
		if ($this->num_rows == 0) {
			$this->last_error = 'no rows found';
			return false;
		}
		else if ($this->num_rows > 1) {
			$this->last_error = 'more than 1 rows found';
			return false;
		}
		
		return mysqli_fetch_assoc($res);
	}
	
	function fetch_assoc_array($sql) {
		$this->reset();
		
		$this->result = mysqli_query($this->link, $sql);
		
		if (!$this->result) {
			$this->last_errno = mysqli_errno($this->link);
			$this->last_error = mysqli_error($this->link);
			if ($this->last_errno == 1062)
				$this->duplicate_entry = true;
    		return false;
		}
		
		$this->num_rows = mysqli_affected_rows($this->link);
		
		if ($this->num_rows == 0) {
			$this->last_error = 'no rows found';
			return false;
		}
		
		$data = array();
		
		while ($row = mysqli_fetch_assoc($this->result)) {
			$data[] = $row;
		}
		
		return $data;
	}
	
	function fetch_assoc_key_array($sql, $keyname) {
		$this->reset();
		
		if (empty($keyname)) {
			$this->last_error = 'no keyname given';
			return false;
		}
		
		$this->result = mysqli_query($this->link, $sql);
		
		if (!$this->result) {
			$this->last_errno = mysqli_errno($this->link);
			$this->last_error = mysqli_error($this->link);
			if ($this->last_errno == 1062)
				$this->duplicate_entry = true;
    		return false;
		}
		
		$this->num_rows = mysqli_affected_rows($this->link);
		
		if ($this->num_rows == 0) {
			$this->last_error = 'no rows found';
			return false;
		}
		
		$data = array();
		
		while ($row = mysqli_fetch_assoc($this->result)) {
			$key = $row[$keyname];
			$data[$key] = $row;
		}
		
		return $data;
	}
}
?>