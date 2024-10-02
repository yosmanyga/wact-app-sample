<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: fielddictionary.inc.php,v 1.1 2004/06/21 22:01:01 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* @see http://wact.sourceforge.net/index.php/FieldNameDictionary
* @access public
* @package WACT_VALIDATION
*/
class DefaultFieldNameDictionary {
	/**
	* @param string name of the field
	* @return string display name of the field
	* @access protected
	*/
	function getFieldName($name) {
	    return $name;
	}
}

?>