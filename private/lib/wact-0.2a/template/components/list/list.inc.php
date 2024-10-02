<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: list.inc.php,v 1.7 2004/11/12 21:25:08 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Represents list tags at runtime, providing an API for preparing the data set
* @see http://wact.sourceforge.net/index.php/ListComponent
* @access public
* @package WACT_COMPONENT
*/
class ListComponent extends DataSourceComponent {

    function ensureDataSourceAvailable() {
		if (!isset($this->_datasource)) {
			require_once WACT_ROOT . 'util/emptydataset.inc.php';
			$this->registerDataSource(new EmptyDataSet());
		}
    }

	/**
	* Registers a dataset with the list component. The dataset must
	* implement the iterator methods defined in DataSet
	* @deprecated
	* @see DataSet
	* @param object implementing the DataSet iterator methods
	* @return void
	* @access public
	*/
	function registerDataSet(&$DataSet) {
		$this->registerDataSource($DataSet);
	}

	/**
	* Prepares the list for iteration, creating an EmptyDataSet if no
	* data set has been registered then calling the dataset reset
	* method.
	* @see EmptyDataSet
	* @return void
	* @access protected
	*/
	function prepare() {
        $this->ensureDataSourceAvailable();
        $this->_datasource->reset();
	}
}
?>