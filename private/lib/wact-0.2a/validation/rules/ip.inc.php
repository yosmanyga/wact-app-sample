<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: ip.inc.php,v 1.3 2004/11/16 01:55:37 jeffmoore Exp $
*/

/**
* Include domain validator
*/
require_once 'domain.inc.php';

/**
* Check for a valid ip address.
* @see http://wact.sourceforge.net/index.php/IPAddressRule
* @access public
* @package WACT_VALIDATION
*/
class IPAddressRule extends SingleFieldRule {

	/**
	* Constructs a IPAddressRule
	* @param string fieldname to validate
	* @param array of acceptable values
	* @access public
	*/
	function IPAddressRule($fieldname) {
		parent :: SingleFieldRule($fieldname);
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (is_integer(strpos($value, '.'))) {
            $quad = explode('.', $value);
            if (count($quad) != 4) {
                $this->Error('IP_INVALID');
            } else {
                foreach($quad as $element) {
                    if (!preg_match('/^\d+$/', $element)) {
                        $this->Error('IP_INVALID');
                        break;
                    }
                    if (intval($element) > 255) {
                        $this->Error('IP_INVALID');
                        break;
                    }
                }
            }
        } else {
            $this->Error('IP_INVALID');
        }
    }
}

/**
* Check for a valid parital IP address.
* @see http://wact.sourceforge.net/index.php/PartialIPAddressRule
* @access public
* @package WACT_VALIDATION
*/
class PartialIPAddressRule extends SingleFieldRule {

	/**
	* Constructs a PartialIPAddressRule
	* @param string fieldname to validate
	* @param array of acceptable values
	* @access public
	*/
	function PartialIPAddressRule($fieldname) {
		parent :: SingleFieldRule($fieldname);
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if ($value{strlen($value)-1} != '.') {
            $this->Error('IP_INVALID');
        } else {
            $quad = explode('.', substr($value, 0, strlen($value)-1));
            if (count($quad) > 4) {
                $this->Error('IP_INVALID');
            } else {
                foreach($quad as $element) {
                    if (!preg_match('/^\d+$/', $element)) {
                        $this->Error('IP_INVALID');
                        break;
                    }
                    if (intval($element) > 255) {
                        $this->Error('IP_INVALID');
                        break;
                    }
                }
            }
        }
    }
}

?>