<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: suffix.inc.php,v 1.3 2004/11/16 01:55:38 jeffmoore Exp $
*/

require_once WACT_ROOT . 'validation/rule.inc.php';

/**
* Check that a field ends with a specific string suffix
* within allowable parameters.
* @see http://wact.sourceforge.net/index.php/PrefixRule
* @access public
* @package WACT_VALIDATION
*/
class SuffixRule extends SingleFieldRule {
	/**
	* @var string
	* @access private
	*/
	var $Suffix;

	/**
	* Constructs SuffixRule
	* @param string fieldname to validate
	* @param string Suffix
	* @access public
	*/
	function SuffixRule($fieldname, $Suffix) {
		parent :: SingleFieldRule($fieldname);
		$this->Suffix = $Suffix;
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (substr($value, -strlen($this->Suffix)) != $this->Suffix) {
            $this->Error('SUFFIX_MISSING', array('suffix' => $this->Suffix));
        }
    }
}



/**
* Check that a field does not end with a specific string suffix
* within allowable parameters.
* @see http://wact.sourceforge.net/index.php/ExcludeSuffixRule
* @access public
* @package WACT_VALIDATION
*/
class ExcludeSuffixRule extends SingleFieldRule {
	/**
	* @var string
	* @access private
	*/
	var $Suffix;

	/**
	* Constructs ExcludeSuffixRule
	* @param string fieldname to validate
	* @param string Suffix
	* @access public
	*/
	function ExcludeSuffixRule($fieldname, $Suffix) {
		parent :: SingleFieldRule($fieldname);
		$this->Suffix = $Suffix;
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (substr($value, -strlen($this->Suffix)) == $this->Suffix) {
            $this->Error('SUFFIX_NOT_ALLOWED', array('suffix' => $this->Suffix));
        }
    }
}

?>