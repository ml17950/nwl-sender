<?php
class clsDB {
	private $dbhost;
	private $dbuser;
	private $dbpass;
	private $dbname;
	private $pdo;
	private $link;
	
	var $result;
	var $last_errno;
	var $last_error;
	var $last_insert_id;
	var $duplicate_entry;
	var $num_rows;
	
	function __construct($dbhost, $dbuser, $dbpass, $dbname) {
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpass = $dbpass;
		$this->dbname = $dbname;

		try {
			$this->pdo = new PDO('mysql:host='.$this->dbhost.';dbname='.$this->dbname.';charset=utf8mb4', $this->dbuser, $this->dbpass);
		} catch (PDOException $e) {
			print "Error!!: ".$e->getMessage()."<br/>";
			die();
		}
	}
	
	function __destruct() {
	}
	
	function reset() {
		$this->last_insert_id = 0;
		$this->last_errno = 0;
		$this->last_error = '';
		$this->duplicate_entry = false;
		$this->num_rows = -1;
	}
	
	function query($sql) {
		$this->reset();

		$statement = $this->pdo->prepare($sql);

		try {
			$this->result = $statement->execute();

			if (!$this->result) {
				$this->last_query = $statement->queryString;
				$this->last_errno = $statement->errorInfo()[1];
				$this->last_error = $statement->errorInfo()[2];
				if ($this->last_errno == 1062)
					$this->duplicate_entry = true;
				elseif ($statement->errorInfo()[0] == 'HY093')
					$this->last_error = 'Invalid parameter number';
				else
					print_r($statement->errorInfo());
				if ($this->last_errno != 1062)
					$this->log_sql_error(__FUNCTION__);
				return false;
			}
		}
		catch (PDOException $e) {
			$this->last_query = $statement->queryString;
			$this->last_errno = $statement->errorInfo()[1];
			$this->last_error = $statement->errorInfo()[2];
			if ($this->last_errno == 1062)
				$this->duplicate_entry = true;
			elseif ($statement->errorInfo()[0] == 'HY093')
				$this->last_error = 'Invalid parameter number';
			else
				print_r($statement->errorInfo());
			if ($this->last_errno != 1062)
				$this->log_sql_error(__FUNCTION__);
			return false;
		}

		$this->last_query		= $sql;
		$this->last_insert_id	= $this->pdo->lastInsertId();
		$this->num_rows			= $statement->rowCount();

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

		$statement = $this->pdo->prepare($sql);

		try {
			$sresult = $statement->execute();

			// if (DEV) { $statement->debugDumpParams(); exit; }

			if (!$sresult) {
				$this->last_query = $statement->queryString;
				$this->last_errno = $statement->errorInfo()[1];
				$this->last_error = $statement->errorInfo()[2];
				if ($this->last_errno == 1062)
					$this->duplicate_entry = true;
				elseif ($statement->errorInfo()[0] == 'HY093')
					$this->last_error = 'Invalid parameter number';
				else
					print_r($statement->errorInfo());
				if ($this->last_errno != 1062)
					$this->log_sql_error(__FUNCTION__);
				return false;
			}
		}
		catch (PDOException $e) {
			$this->last_query = $statement->queryString;
			$this->last_errno = $statement->errorInfo()[1];
			$this->last_error = $statement->errorInfo()[2];
			if ($this->last_errno == 1062)
				$this->duplicate_entry = true;
			elseif ($statement->errorInfo()[0] == 'HY093')
				$this->last_error = 'Invalid parameter number';
			else
				print_r($statement->errorInfo());
			if ($this->last_errno != 1062)
				$this->log_sql_error(__FUNCTION__);
			return false;
		}

		$this->num_rows = $statement->rowCount();

		if ($this->num_rows == 0) {
			$this->last_error = 'no rows found';
			return false;
		}
		else if ($this->num_rows > 1) {
			$this->last_error = 'more than 1 rows found';
			return false;
		}

		return $statement->fetch(PDO::FETCH_ASSOC);
	}
	
	function fetch_assoc_array($sql) {
		$this->reset();

		$statement = $this->pdo->prepare($sql);

		try {
			$sresult = $statement->execute();

			// if (DEV) { $statement->debugDumpParams(); exit; }

			if (!$sresult) {
				$this->last_query = $statement->queryString;
				$this->last_errno = $statement->errorInfo()[1];
				$this->last_error = $statement->errorInfo()[2];
				if ($this->last_errno == 1062)
					$this->duplicate_entry = true;
				elseif ($statement->errorInfo()[0] == 'HY093')
					$this->last_error = 'Invalid parameter number';
				else
					print_r($statement->errorInfo());
				if ($this->last_errno != 1062)
					$this->log_sql_error(__FUNCTION__);
				return false;
			}
		}
		catch (PDOException $e) {
			$this->last_query = $statement->queryString;
			$this->last_errno = $statement->errorInfo()[1];
			$this->last_error = $statement->errorInfo()[2];
			if ($this->last_errno == 1062)
				$this->duplicate_entry = true;
			elseif ($statement->errorInfo()[0] == 'HY093')
				$this->last_error = 'Invalid parameter number';
			else
				print_r($statement->errorInfo());
			if ($this->last_errno != 1062)
				$this->log_sql_error(__FUNCTION__);
			return false;
		}

		$this->num_rows = $statement->rowCount();

		if ($this->num_rows == 0) {
			$this->last_error = 'no rows found';
			return false;
		}

		$data = array();

		while ($row = $statement->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
			$data[] = $row;
		}

		return $data;
	}
	
	function fetch_assoc_key_array($sql, $keyname) {
		$this->reset();

		$statement = $this->pdo->prepare($sql);

		try {
			$sresult = $statement->execute();

			// if (DEV) { $statement->debugDumpParams(); exit; }

			if (!$sresult) {
				$this->last_query = $statement->queryString;
				$this->last_errno = $statement->errorInfo()[1];
				$this->last_error = $statement->errorInfo()[2];
				if ($this->last_errno == 1062)
					$this->duplicate_entry = true;
				elseif ($statement->errorInfo()[0] == 'HY093')
					$this->last_error = 'Invalid parameter number';
				else
					print_r($statement->errorInfo());
				if ($this->last_errno != 1062)
					$this->log_sql_error(__FUNCTION__);
				return false;
			}
		}
		catch (PDOException $e) {
			$this->last_query = $statement->queryString;
			$this->last_errno = $statement->errorInfo()[1];
			$this->last_error = $statement->errorInfo()[2];
			if ($this->last_errno == 1062)
				$this->duplicate_entry = true;
			elseif ($statement->errorInfo()[0] == 'HY093')
				$this->last_error = 'Invalid parameter number';
			else
				print_r($statement->errorInfo());
			if ($this->last_errno != 1062)
				$this->log_sql_error(__FUNCTION__);
			return false;
		}

		$this->num_rows = $statement->rowCount();

		if ($this->num_rows == 0) {
			$this->last_error = 'no rows found';
			return false;
		}

		$data = array();

		while ($row = $statement->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
			$key = $row[$keyname];
			$data[$key] = $row;
		}

		return $data;
	}
}
?>