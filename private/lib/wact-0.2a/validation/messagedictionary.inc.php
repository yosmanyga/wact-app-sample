<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: messagedictionary.inc.php,v 1.1 2004/06/21 22:01:01 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* The ErrorMessage dictionary contains a map of error messages to be displayed
* to the end user, if they fail any of the validation rules assigned to a controller
* @see http://wact.sourceforge.net/index.php/ErrorMessageDictionary
* @access public
* @package WACT_VALIDATION
*/
class ErrorMessageDictionary {
	/**
	* Associative array of errors, key being the error id and the value the
	* message for the end user.
	* @var array
	* @access private
	*/
	var $ErrorMessages = array();

	/**
	* Gets a message, given it's group and id. This method loads the var file
	* not already loaded, using the importVarFile() function.
	* @param string group identifies var file name: /errormessages/$Group.vars
	* @param string id of error message e.g. "MISSING"
	* @return string error message to display to user
	* @access protected
	*/
	function getMessage($Group, $Id) {
		if (!isset($this->ErrorMessages[$Group])) {
			$this->ErrorMessages[$Group] = importVarFile("/errormessages/$Group.vars");
		}
	
		return $this->ErrorMessages[$Group][$Id];
	}
}

?>