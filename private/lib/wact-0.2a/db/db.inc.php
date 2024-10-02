<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_DB
* @version $Id: db.inc.php,v 1.16 2004/11/20 17:23:16 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Define globals
*/
$GLOBALS['DatabaseConnectionObj'] = NULL;


class DBC_ConnectionConfiguration {

    var $driver_name;
    
    function DBC_ConnectionConfiguration($driver_name) {
        $this->driver_name = $driver_name;
    }

    function get($option) {
        return ConfigManager::getOption('config', 'database', $this->driver_name . '.' . $option);
    }
}

/**
* Manages a single database connection to provide shortcut syntax for applications
* which access only a single database.  ( A common case ).
* This class is not instantiated as is used only as a namespace for the methods it
* contains.
* The methods of this class mostly mirror those of a database connection with the
* addition of some handy factory methods.
* @see http://wact.sourceforge.net/index.php/DBC
* @access public
* @package WACT_DB
*/
class DBC {

    /**
    * Return the current database connection managed by this class
    * @return Connection reference
    * @access public
    */
    function &getCurrentConnection() {
        if (!isset($GLOBALS['DatabaseConnectionObj'])) {
            $driver = ConfigManager::getOption('config', 'database', 'driver');
            require_once(WACT_ROOT . 'db/drivers/' . $driver . '.inc.php');
            $driverClass = $driver . 'Connection';
            $GLOBALS['DatabaseConnectionObj'] =& 
                new $driverClass(new DBC_ConnectionConfiguration($driver));
        }
        return $GLOBALS['DatabaseConnectionObj'];
    }
    
    /**
    * Factory function to create a MySQLRecord object
    * @see http://wact.sourceforge.net/index.php/NewRecord
    * @param DataSpace or subclass (optional) 
    *   used to initialize the fields of the new record prior to calling insert()
    * @return MySqlRecord reference
    * @access public
    */
    function &NewRecord($DataSpace = NULL) {
        $connection =& DBC::getCurrentConnection();
        return $connection->NewRecord($DataSpace);
    }

    /**
    * Factory function used to retrieve more than one row from a MySQL database,
    * applying a filter to the data if supplied as an argument
    * @see http://wact.sourceforge.net/index.php/NewRecordSet
    * @param string SQL statement
    * @param object filter class (optional)
    * @return MySqlRecordSet reference
    * @access public
    */
    function &NewRecordSet($query, $filter = NULL) {
        $connection =& DBC::getCurrentConnection();
        return $connection->NewRecordSet($query, $filter);
    }

    /**
    * Factory function used to retrieve more than one row from a MySQL database,
    * applying a filter to the data if supplied as an argument, and applying a
    * pager to the result set as well.
    * @param string SQL statement
    * @param object pager
    * @param object filter class (optional)
    * @return MySqlRecordSet reference
    * @access public
    */
    function &NewPagedRecordSet($query, &$pager, $filter = NULL) {
        $connection =& DBC::getCurrentConnection();
        return $connection->NewPagedRecordSet($query, $pager, $filter);
    }

	/**
	* Retreive an array where each element of the array is the value from the
	* first column of a database query.
	* @param string SQL Query
	* @access public
	*/
    function getOneColumnArray($query) {
        $connection =& DBC::getCurrentConnection();
        return $connection->getOneColumnArray($query);
    }
    
	/**
	* Retreive an associative array where each element of the array is based
	* on the first column as a key and the second column as data.
	* @param string SQL Query
	* @access public
	*/
    function getTwoColumnArray($query) {
        $connection =& DBC::getCurrentConnection();
        return $connection->getTwoColumnArray($query);
    }
    
	/**
	* Retreive a single record from the database based on a query.
	* @param string SQL Query
	* @access public
	*/
    function &FindRecord($query) {
        $connection =& DBC::getCurrentConnection();
        return $connection->FindRecord($query);
    }
 
	/**
	* Get a single value from the first column of a single record from
	* a database query.
	* @param string SQL Query
	* @access public
	*/
    function getOneValue($query) {
        $connection =& DBC::getCurrentConnection();
        return $connection->getOneValue($query);
    }

	/**
	* Performs any query that does not return a cursor.
	* @param string SQL query
	* @return resource MySQL result resource
	* @access public
	*/
    function execute($query) {
        $connection =& DBC::getCurrentConnection();
        return $connection->execute($query);
    }
    
    /**
	* EXPERIMENTAL:
	* Convert a PHP value into an SQL literal.
	* @return resource MySQL result resource
	* @param mixed value to convert
	* @param string (optional) type to convert to
	* @access public
	*/
    function makeLiteral($value, $type = NULL) {
        $connection =& DBC::getCurrentConnection();
        return $connection->makeLiteral($value, $type);
    }
}

?>