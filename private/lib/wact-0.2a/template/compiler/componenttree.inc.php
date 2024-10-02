<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: componenttree.inc.php,v 1.11 2004/07/25 03:51:26 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* The root compile time component in the template hierarchy. 
* @see http://wact.sourceforge.net/index.php/ComponentTree
* @access public
* @package WACT_TEMPLATE
*/
class ComponentTree extends CompilerDirectiveTag {
	/**
	* List of used TAG ids for duplicate checks
	* from TreeBuilder::checkServerId()
	* @var array;
	* @access private
	*/
	var $tagIds = array();
	/**
	* Calls the parent preGenerate() method then writes
	* the prepare method to the compiled template.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$code->writePHP($this->getComponentRefCode() . '->prepare();');
	}

	/**
	* Returns the base for building the PHP runtime component reference string
	* @param CodeWriter
	* @return string
	* @access protected
	*/
	function getComponentRefCode() {
		return '$root';
	}

	/**
	* @param CodeWriter
	* @return string
	* @access protected
	*/
	function getDataSourceRefCode() {
		return '$root';
	}

	/**
	* Returns this instance of ComponentTree
	* @return ComponentTree this instance
	* @access protected
	*/
	function &getDataSource() {
		return $this;
	}

	/**
	* @return Boolean Indicating whether or not this component is a DataSource
	*/
	function isDataSource() {
	    return TRUE;
	}

}
?>