<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: pattern.inc.php,v 1.4 2004/11/16 01:55:38 jeffmoore Exp $
*/

require_once WACT_ROOT . 'validation/rule.inc.php';

/**
* Check that a field matches a specific string pattern
* within allowable parameters.
* @see http://wact.sourceforge.net/index.php/PatternRule
* @access public
* @package WACT_VALIDATION
*/
class PatternRule extends SingleFieldRule {
	/**
	* @var string
	* @access private
	*/
	var $Pattern;
	/**
	* @var string
	* @access private
	*/
	var $Id;

	/**
	* Constructs PatternRule
	* @param string fieldname to validate
	* @param string Pattern
	* @access public
	*/
	function PatternRule($fieldname, $Pattern, $Group = 'validation', $Id = 'INVALID') {
		parent :: SingleFieldRule($fieldname);
		$this->Pattern = $Pattern;
		$this->Id = $Id;
		$this->Group = $Group;
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (!preg_match($this->Pattern, $value)) {
            $this->Error($this->Id);
        }
    }
}



/**
* Check that a field does not match a specific string pattern
* within allowable parameters.
* @see http://wact.sourceforge.net/index.php/ExcludePatternRule
* @access public
* @package WACT_VALIDATION
*/
class ExcludePatternRule extends SingleFieldRule {
	/**
	* @var string
	* @access private
	*/
	var $Pattern;
	/**
	* @var string
	* @access private
	*/
	var $Id;

	/**
	* Constructs ExcludePatternRule
	* @param string fieldname to validate
	* @param string Pattern
	* @access public
	*/
	function ExcludePatternRule($fieldname, $Pattern, $Group = 'validation', $Id = 'INVALID') {
		parent :: SingleFieldRule($fieldname);
		$this->Pattern = $Pattern;
		$this->Id = $Id;
		$this->Group = $Group;
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (preg_match($this->Pattern, $value)) {
            $this->Error($this->Id);
        }
    }
}

?>