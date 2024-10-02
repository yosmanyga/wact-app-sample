<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: match.inc.php,v 1.9 2004/11/16 01:55:37 jeffmoore Exp $
*/

require_once WACT_ROOT . 'validation/rule.inc.php';

/**
* The field being validated must exactly match a reference field
* @see http://wact.sourceforge.net/index.php/MatchRule
* @access public
* @package WACT_VALIDATION
*/
class MatchRule extends Rule {
	/**
	* Reference field to match against
	* @var string
	* @access private
	*/
	var $refField;
	/**
	* Field name to validate
	* @var string
	* @access private
	*/
    var $fieldname;

	/**
	* Constructs MatchRule
	* @param string fieldname to validate
	* @param string reference field in DataSource to match against
	* @access public
	*/
	function MatchRule($fieldname, $referenceField) {
        $this->fieldname = $fieldname;
		$this->refField = $referenceField;
	}

	/**
	* Performs validation
	* @param DataSource - data to validate
	* @param ErrorList
	* @return boolean TRUE if validation passed
	* @access public
	*/
	function validate(&$DataSource, &$ErrorList) {
		$value1 = $DataSource->get($this->fieldname);
		$value2 = $DataSource->get($this->refField);
		if (isset($value1) && isset($value2)) {
			if (strcmp($value1, $value2)) {
                $ErrorList->addError($this->Group, 'NO_MATCH', 
                    array('Field' => $this->fieldname, 'MatchField' => $this->refField));
				return FALSE;
			}
		}
		return TRUE;
	}
}
?>