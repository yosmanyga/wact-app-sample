<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: member.inc.php,v 1.9 2004/11/16 01:55:37 jeffmoore Exp $
*/

require_once WACT_ROOT . 'validation/rule.inc.php';

/**
* Check a field for membership in a list of allowable values.
* @see http://wact.sourceforge.net/index.php/MemberRule
* @access public
* @package WACT_VALIDATION
*/
class MemberRule extends SingleFieldRule {
	/**
	* Member list to validate against
	* @var array
	* @access private
	*/
	var $memberList;

	/**
	* Constructs a MatchRule
	* @param string fieldname to validate
	* @param array of acceptable values
	* @access public
	*/
	function MemberRule($fieldname, $list) {
		parent :: SingleFieldRule($fieldname);
		
		$this->memberList = $list;
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (!array_key_exists($value, $this->memberList)) {
            $this->Error('NON_MEMBER', array('Value' => $value));
        }
	}
}
?>