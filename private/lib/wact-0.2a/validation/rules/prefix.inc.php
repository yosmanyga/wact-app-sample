<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: prefix.inc.php,v 1.3 2004/11/16 01:55:38 jeffmoore Exp $
*/

require_once WACT_ROOT . 'validation/rule.inc.php';

/**
* Check that a field begins with a specific string prefix
* within allowable parameters.
* @see http://wact.sourceforge.net/index.php/PrefixRule
* @access public
* @package WACT_VALIDATION
*/
class PrefixRule extends SingleFieldRule {
	/**
	* @var string
	* @access private
	*/
	var $Prefix;

	/**
	* Constructs PrefixRule
	* @param string fieldname to validate
	* @param string Prefix
	* @access public
	*/
	function PrefixRule($fieldname, $Prefix) {
		parent :: SingleFieldRule($fieldname);
		$this->Prefix = $Prefix;
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (substr($value, 0, strlen($this->Prefix)) != $this->Prefix) {
            $this->Error('PREFIX_MISSING', array('prefix' => $this->Prefix));
        }
    }
}



/**
* Check that a field does not begin with a specific string prefix
* within allowable parameters.
* @see http://wact.sourceforge.net/index.php/ExcludePrefixRule
* @access public
* @package WACT_VALIDATION
*/
class ExcludePrefixRule extends SingleFieldRule {
	/**
	* @var string
	* @access private
	*/
	var $Prefix;

	/**
	* Constructs ExcludePrefixRule
	* @param string fieldname to validate
	* @param string Prefix
	* @access public
	*/
	function ExcludePrefixRule($fieldname, $Prefix) {
		parent :: SingleFieldRule($fieldname);
		$this->Prefix = $Prefix;
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (substr($value, 0, strlen($this->Prefix)) == $this->Prefix) {
            $this->Error('PREFIX_NOT_ALLOWED', array('prefix' => $this->Prefix));
        }
    }
}

?>