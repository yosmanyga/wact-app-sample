<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: datasetdecorator.inc.php,v 1.6 2004/11/26 18:24:43 jeffmoore Exp $
*/

/**
* Provides a base class to use for decorating DataSpaces
* @see http://wact.sourceforge.net/index.php/DataSpaceDecorator
* @access public
* @package WACT_UTIL
*/
class DataSpaceDecorator /* implements DataSource */ {
	/**
	* The data set being decorated
	* @var object
	* @access private
	*/
	var $dataset;

	/**
	* Constructs DataSetDecorator
	* @param object data set to decorate
	* @access public
	*/
	function DataSpaceDecorator(&$dataset) {
		$this->dataset =& $dataset;
	}

	function get($name) {
		return $this->dataset->get($name);
	}

	function set($name, $value) {
		$this->dataset->set($name, $value);
	}

    function remove($name) {
        $this->dataset->remove($name);
    }

    function removeAll() {
        $this->dataset->removeAll();
    }

    function import($property_list) {
        $this->dataset->import($property_list);
    }
        
	function merge($property_list) {
		$this->dataset->merge($property_list);
	}

	function &export() {
		return $this->dataset->export();
	}
	
	function isDataSource() {
	    return TRUE;
	}
	
    function hasProperty($name) {
		return $this->dataset->hasProperty($name);
    }
	
	/**
	* @param object instance of filter class containing a doFilter() method
	* @return void
	* @access public
	*/
	function registerFilter(&$filter) {
		$this->dataset->registerFilter($filter);
	}
	
	/**
	* @return void
	* @access protected
	*/
	function prepare() {
		$this->dataset->prepare();
	}
}


/**
* Provides a base class to use for decorating datasets
* @see http://wact.sourceforge.net/index.php/DataSetDecorator
* @access public
* @package WACT_UTIL
*/
class DataSetDecorator extends DataSpaceDecorator /* implements Interator */ {

	/**
	* Constructs DataSetDecorator
	* @param object data set to decorate
	* @access public
	*/
	function DataSetDecorator(&$dataset) {
	    parent::DataSpaceDecorator($dataset);
	}

	function reset() {
		$this->dataset->reset();
	}

	function next() {
		return $this->dataset->next();
	}

}
?>