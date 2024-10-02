<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: emptydataset.inc.php,v 1.6 2004/06/15 03:03:50 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* A null implementation of the DataSpace and Iterator
* @see http://wact.sourceforge.net/index.php/EmptyDataSet
* @access public
* @package WACT_UTIL
*/
class EmptyDataSet /* implements DataSource, Iterator */ {

	//--------------------------------------------------------------------------------
	// Iterator implementation
	/**
	* Iterator Method
	* @return void
	* @access public
	*/
	function reset() {
	}
	/**
	* Iterator Method
	* @return boolean FALSE
	* @access public
	*/
	function next() {
		return FALSE;
	}
	
	//--------------------------------------------------------------------------------
	// DataSource implementation

    function get($name) {
    }

    function set($name, $value) {
    }
    
    function remove($name) {
    }
    
    function removeAll() {
    }

    function import($property_list) {
	}

    function merge($property_list) {
    }
    
    function &export() {
		return array();
	}

    function isDataSource() {
        return TRUE;
    }

	function registerFilter(&$filter) {
	}

	function prepare() {
	}
}
?>