<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_DB
* @version $Id: adodb.inc.php,v 1.26 2004/11/20 18:09:47 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Make sure dataspace is loaded
*/
if (!class_exists('DataSpace')) {
	require WACT_ROOT . 'util/dataspace.inc.php';
}

/**
* Include External Library ADOdb
*/
if ( !defined('ADODB_LIBRARY_PATH') ) {
    define('ADODB_LIBRARY_PATH', ConfigManager::getOptionAsPath('config', 'adodb', 'library_path'));
}
if (!@include_once ADODB_LIBRARY_PATH . 'adodb.inc.php') {
    RaiseError('runtime', 'LIBRARY_REQUIRED', array(
        'library' => 'ADOdb',
        'path' => ADODB_LIBRARY_PATH));
}

//--------------------------------------------------------------------------------
/**
* Encapsulates a database connection.  Allows for lazy connections.
* implements Connection interface
* @see Connection
* @see http://wact.sourceforge.net/index.php/Connection
* @access public
* @package WACT_DB
*/
class AdodbConnection {
	/**
	* ADOdb DB Connection object
	* @var object subclass of ADOConnection
	* @access private
	*/
	var $ConnectionId;

	/**
	* Configuration information
	* @var string
	* @access private
	*/
	var $config;

	/**
	* Create a ADOdb database connection.
    * @param object Connection Configuration information
	* @access private
	*/
	function AdodbConnection(&$config) {
	    $this->config =& $config;
	}

	/**
	* Return connectionId for a ADOdb database.  Allow a lazy connection.
	* @return object ADOConnection
	* @access protected
	*/
	function & getConnectionId() {
		if (!isset($this->ConnectionId)) {
			$this->connect();
		}
		return $this->ConnectionId;
	}

	/**
	* Connect to the the database
	* @return void
	* @access protected
	*/
	function connect() {
		$this->ConnectionId = & ADONewConnection($this->config->get('dbtype'));
		$this->ConnectionId->SetFetchMode(ADODB_FETCH_NUM);
		$this->ConnectionId->Connect(
            $this->config->get('host'),
            $this->config->get('user'),
            $this->config->get('password'),
            $this->config->get('database'));
		if (!$this->ConnectionId) {
			$this->RaiseError();
		}
	}

	/**
	* Raises an error, passing it on to the framework level error mechanisms
	* @param string (optional) SQL statement
	* @return void
	* @access private
	*/
	function RaiseError($sql = NULL) {
		$error = & $this->getConnectionId();
		$id = 'DB_ERROR';
		$info = array('driver' => 'ADOdb');
		$errno = $error->ErrorNo();
		if ($errno != -1) {
			$info['errorno'] = $errno;
			$info['error'] = $error->ErrorMsg();;
			$id .= '_MESSAGE';
		}
		if (!is_null($sql)) {
			$info['sql'] = $sql;
			$id .= '_SQL';
		}
		RaiseError('db', $id, $info);
	}

	/**
	* Convert a PHP value into an SQL literal.  The type to convert to is
	* based on the type of the PHP value passed, or the type string passed.
	* @param mixed value to convert
	* @param string (optional) type to convert to
	*   Allowable types are: boolean, string, int, float.
	* @return string literal SQL fragment
	* @access public
	*/
	function makeLiteral($value, $type = NULL) {
        if (is_null($value)) {
            return 'NULL';
        }
		if (is_null($type)) {
			$type = gettype($value);
		}
		switch (strtolower($type)) {
            case 'string':
                $conn = & $this->getConnectionId();
                return $conn->qstr($value);
            case 'boolean':
                return ($value) ? 1 : 0;
            default:
                return strval($value);
		}
	}

    /**
    * Factory function to create a Record object
    * @see http://wact.sourceforge.net/index.php/NewRecord
    * @param DataSpace or subclass (optional)
    *   used to initialize the fields of the new record prior to calling insert()
    * @return Record reference
    * @access public
    */
    function &NewRecord($DataSpace = NULL) {
        $Record =& new AdodbRecord($this);
        if (!is_null($DataSpace)) {
            $Record->import($DataSpace->export());
        }
        return $Record;
    }

    /**
    * Factory function used to retrieve more than one row from a Adodb database,
    * applying a filter to the data if supplied as an argument
    * @see http://wact.sourceforge.net/index.php/NewRecordSet
    * @param string SQL statement
    * @param object filter class (optional)
    * @return RecordSet reference
    * @access public
    */
    function &NewRecordSet($query, $filter = NULL) {
        $RecordSet =& new AdodbRecordSet($this, $query);
        if (!is_null($filter)) {
            $RecordSet->registerFilter($filter);
        }
        return $RecordSet;
    }

    /**
    * Factory function used to retrieve more than one row from a Adodb database,
    * applying a filter to the data if supplied as an argument, and applying a
    * pager to the result set as well.
    * @param string SQL statement
    * @param object pager
    * @param object filter class (optional)
    * @return RecordSet reference
    * @access public
    */
    function &NewPagedRecordSet($query, &$pager, $filter = NULL) {
        $RecordSet =& $this->NewRecordSet($query, $filter);
        $RecordSet->paginate($pager);
        return $RecordSet;
    }

	/**
	* Retreive a single record from the database based on a query.
	* @param string SQL Query
	* @return Record object or NULL if not found
	* @access public
	*/
	function &FindRecord($query) {
		$Record =& new AdodbRecord($this);
		$QueryId = $this->_execute($query);
		$Record->properties =& $QueryId->fetchRow();
		$QueryId->Close();
		if (is_array($Record->properties)) {
			return $Record;
		}
	}

	/**
	* Get a single value from the first column of a single record from
	* a database query.
	* @param string SQL Query
	* @return Value or NULL if not found
	* @access public
	*/
	function getOneValue($query) {
		$QueryId = $this->_execute($query);
		$row = $QueryId->fetchRow();
		$QueryId->Close();
		if (is_array($row)) {
			return $row[0];
		}
	}

	/**
	* Retreive an array where each element of the array is the value from the
	* first column of a database query.
	* @param string SQL Query
	* @access public
	*/
	function getOneColumnArray($query) {
		$Column = array();
		$QueryId = $this->_execute($query);
		while (is_array($row = $QueryId->fetchRow())) {
			$Column[] = $row[0];
		}
		$QueryId->Close();
		return $Column;
	}

	/**
	* Retreive an associative array where each element of the array is based
	* on the first column as a key and the second column as data.
	* @param string SQL Query
	* @access public
	*/
	function getTwoColumnArray($query) {
		$Column = array();
		$QueryId = $this->_execute($query);
		while (is_array($row = $QueryId->fetchRow())) {
			$Column[$row[0]] = $row[1];
		}
		$QueryId->Close();
		return $Column;
	}

	/**
	* Performs any query that does not return a cursor.
	* @param string SQL query
	* @return boolean TRUE if query is successful
	*/
	function execute($sql) {
	    return (Boolean) $this->_execute($sql);
	}

	/**
	* For internal driver use only
	* @param string SQL query
	* @return mixed ADOdb result object or false on error
	* @access public
	*/
	function _execute($sql) {
		$conn = & $this->getConnectionId();
		$result = & $conn->query($sql);
		if (!$result) {
			$this->RaiseError($sql);
		}
		return $result;
	}

	/**
	* Disconnect from database
	* @return void
	* @access public
	*/
	function disconnect() {
	    if (is_object($this->ConnectionId)) {
    		$this->ConnectionId->Close();
    		$this->ConnectionId = NULL;
        }
	}

}

/**
* Encapsulates operations on a database via ADOdb. Generally this
* class is only used for INSERT, UPDATE and DELETE operations
* implements Record interface
* @see Record
* @see http://wact.sourceforge.net/index.php/Record
* @access public
* @package WACT_DB
*/
class AdodbRecord extends DataSpace {
	/**
	* Database connection encasulated in AdodbConnection
	* @var AdodbConnection instance
	* @access private
	*/
	var $Connection;

	/**
	* Construct a record
	* @param AdodbConnection
	*/
	function AdodbRecord(& $Connection) {
		$this->Connection = & $Connection;
		$conn = & $this->Connection->getConnectionId();
		$conn->SetFetchMode(ADODB_FETCH_ASSOC);
	}

	/**
	* Build a list of values to assign to columns
	* @param array associative of field_name => type
	* @param array associative (optional)  of field_name => value
	* @return array List of values to assign
	* @access protected
	*/
	function buildAssignmentList($fields, $extrafields) {
		$queryParams = array();
		foreach ($fields as $fieldname => $type) {
			if (!is_string($fieldname)) {
				$fieldname = $type; // Swap if no type is specified
				$type = NULL;
			}
			$queryParams[$fieldname] = $this->Connection->makeLiteral(
			    $this->get($fieldname), $type
            );
		}
		if (!is_null($extrafields)) {
			foreach ($extrafields as $fieldname => $value) {
				$queryParams[$fieldname] = $value;
			}
		}
		return $queryParams;
	}

	/**
	* INSERT a record into a table with a primary key represented by a 
	* auto_increment/serial column and return the primary key of the 
	* inserted record.
	* the field list parameter allows expressions to defined in the sql
	* statements as well as field values defined in the record.
	* @param string table name
	* @param array associative of field_name => type
	* @param string Name of primary key field field
	* @param array associative (optional)  of field_name => value
	* @return integer Primary key of the newly inserted record or FALSE if no
	*   record was inserted.
	*/
	function insertId($table, $fields, $primary_key_field, $extrafields = NULL) {

        $valueList = $this->buildAssignmentList($fields, $extrafields);
		$query = 'INSERT INTO ' . $table . 
		    ' (' . implode(',', array_keys($valueList)) .') VALUES' . 
		    ' (' . implode(',', $valueList) . ')';

		$result = $this->Connection->execute($query);
		if ($result) {
            $conn = & $this->Connection->getConnectionId();
            $insertId = $conn->Insert_ID();
            if ( $insertId ) {
                return $insertId;
            }
		} else {
		    return FALSE;
		}
	}

	/**
	* INSERTs the values of this record into a single table
	* the field list parameter allows expressions to defined in the sql
	* statements as well as field values defined in the record.
	* @param string table name
	* @param array associative of field_name => type
	* @param string (default = null) Name of autoincrement field
	* @param array associative (optional)  of field_name => value
	* @return Boolean True on success.
	*/
	function insert($table, $fields, $extrafields = NULL)  {
        $valueList = $this->buildAssignmentList($fields, $extrafields);
		$query = 'INSERT INTO ' . $table . 
		    ' (' . implode(',', array_keys($valueList)) .') VALUES' . 
		    ' (' . implode(',', $valueList) . ')';
		return (Boolean) $this->Connection->execute($query);
	}

	/**
	* Performs an UPDATE on a single table
	* @param string table name
	* @param array associative of field_name => type
	* @param string (optional) SQL where clause
	* @param array associative (optional)  of field_name => value
	* @return boolean true on success, false on failure
	* @access public
	*/
	function update($table, $fields, $where = NULL, $extrafields = NULL) {
        $valueList = $this->buildAssignmentList($fields, $extrafields);
		$query = 'UPDATE ' . $table . ' SET ';
        $sep = '';
        foreach ($valueList as $key => $value) {
            $query .= $sep . $key .'='. $value;
            $sep = ', ';
        }
		if (!is_null($where)) {
			$query .= ' WHERE ' . $where;
		}
		return (Boolean) $this->Connection->execute($query);
	}

	/**
	* Gets the number of rows changed by a query
	* @return int number of affected rows
	* @access public
	*/
	function getAffectedRowCount() {
		$QueryId = & $this->Connection->getConnectionId();
		return $QueryId->Affected_Rows();
	}

}

/**
* Encapsulates the results of a SELECT, SHOW, DESCRIBE or EXPLAIN sql statement
* Implements the Iterator interface defined in the DataSpace
* implements RecordSet and PagedDataSet interfaces
* @see RecordSet
* @see PagedDataSet
* @see http://wact.sourceforge.net/index.php/RecordSet
* @access public
* @package WACT_DB
*/
class AdodbRecordSet extends AdodbRecord {
	/**
	* ADOdb result object
	* @var object
	* @access private
	*/
	var $QueryId;

	/**
	* Pager
	* @var object The current pager for this query.
	* @access private
	*/
	var $pager;

	/**
	* SQL Statement
	* @var string
	* @access private
	*/
	var $Query;

	/**
	* Switch to watch if this is the first row
	* @var boolean (default = TRUE)
	* @access private
	*/
	var $first = TRUE;

	/**
	* Switch to watch for resets
	* @var boolean (default = FALSE)
	* @access private
	*/
	var $reentry = FALSE;

	/**
	* Construct a record set.
	* @param object AdodbConnection
	* @param string SQL SELECT, SHOW, DESCRIBE, or EXPLAIN statement
	* @access public
	*/
	function AdodbRecordSet($Connection, $Query_String) {
		$this->Connection = $Connection;
		$conn = & $this->Connection->getConnectionId();
		$conn->SetFetchMode(ADODB_FETCH_ASSOC);
		$this->Query = $Query_String;
	}

	/**
	* Stores the SQL statement and makes sure the result object is
	* empty
	* @param string SQL statement
	* @return void
	* @access protected
	*/
	function query($Query_String) {
		$this->freeQuery();
		$this->Query = $Query_String;
	}

	/**
	* Assign a pager to this query for the purposes of breaking up the resulting
	* cursor into paged chucks.
	* @param interface Pager
	* @return void
	* @access public
	*/
	function paginate(&$pager) {
		$this->pager =& $pager;
		$pager->setPagedDataSet($this);
	}

	/**
	* Frees up the Result object if one exists
	* @return void
	* @access private
	*/
	function freeQuery() {
		if (isset($this->QueryId) && is_object($this->QueryId)) {
			$this->QueryId->Close();
			$this->QueryId = NULL;
		}
	}

	/**
	* Move the current pointer to the first position in the cursor.
	* @return void
	* @access public
	*/
	function reset() {
		if (isset($this->QueryId) && is_resource($this->QueryId)) {
			if ($this->QueryId->move(0) === FALSE) {
				$this->Connection->RaiseError();
			}
		} else {
			$query = $this->Query;
			if (isset($this->pager)) {
				$conn = & $this->Connection->getConnectionId();
				$this->QueryId = $conn->SelectLimit($query,
					$this->pager->getItemsPerPage(),$this->pager->getStartingItem());
				if ( !$this->QueryId ) {
					$this->Connection->RaiseError($query);
				}
			} else {
				$this->QueryId = $this->Connection->_execute($query);
			}
			return TRUE;
		}
	}

	/**
	* Iterator next method
	* @return boolean TRUE if there are more results to fetch
	* @access public
	*/
	function next() {
		if (!isset($this->QueryId)) {
			return FALSE;
		}
		$this->properties = $this->QueryId->fetchRow();
		if (is_array($this->properties)) {
			$this->prepare();
			return TRUE;
		} else {
			$this->freeQuery();
			return FALSE;
		}
	}

	/**
	* Returns the number of rows in a query
	* @return int number of rows
	* @access public
	*/
	function getRowCount() {
		return $this->QueryId->RecordCount();
	}

	/**
	* Returns the total number of rows that a query would return, ignoring paging
	* restrictions.  Query re-writing based on _adodb_getcount.
	* @return int number of rows
	* @access public
	*/
	function getTotalRowCount() {
		if (!(preg_match("/^\s*SELECT\s+DISTINCT/is", $this->Query) && preg_match('/\s+GROUP\s+BY\s+/is',$this->Query))) {
			$rewritesql = preg_replace(
						'/^\s*SELECT\s.*\s+FROM\s/Uis','SELECT COUNT(*) FROM ',$this->Query);
			$rewritesql = preg_replace('/(\sORDER\s+BY\s.*)/is','',$rewritesql);
			$conn = & $this->Connection->getConnectionId();
			$conn->SetFetchMode(ADODB_FETCH_NUM);
			$QueryId = $this->Connection->_execute($rewritesql);
			$row = $QueryId->fetchRow();
			$QueryId->Close();
			if (is_array($row)) {
				return $row[0];
			}
		}

		// could not re-write the query, try a different method.
		$QueryId = $this->Connection->_execute($this->Query);
		$count = $QueryId->RecordCount();
		$QueryId->free();
		return $count;
	}
}
?>