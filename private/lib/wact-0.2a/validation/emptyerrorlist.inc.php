<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: emptyerrorlist.inc.php,v 1.1 2004/06/21 22:01:00 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* Returned by FormComponent::getErrorList() if not errors have been defined.
* Provides an "dummy" implementation of the DataSpace iterator and the
* ErrorList
* @see ErrorList
* @see FormComponent
* @see http://wact.sourceforge.net/index.php/EmptyErrorList
* @see http://wact.sourceforge.net/index.php/Iterator
* @access public
* @package WACT_VALIDATION
*/
class EmptyErrorList {
	/**
	* Dummy prepare method does nothing
	* @return void
	* @access protected
	*/
	function prepare() {
	}

	/**
	* Dummy reset method does nothing
	* @return void
	* @access protected
	*/
	function reset() {
	}

	/**
	* Dummy next method
	* @return boolean always returns FALSE
	* @access protected
	*/
	function next() {
		return FALSE;
	}

	/**
	* Dummy restrictFields method
	* @return void
	* @access protected
	*/
	function restrictFields($fieldlist) {
	}

    function removeRestrictions() {
    }
}

?>