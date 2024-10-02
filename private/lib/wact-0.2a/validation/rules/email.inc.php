<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: email.inc.php,v 1.6 2004/11/16 01:55:37 jeffmoore Exp $
*/
/**
* Include parent
*/
require_once 'domain.inc.php';

/**
* Check for a valid email address.
* @see http://wact.sourceforge.net/index.php/EmailRule
* @access public
* @package WACT_VALIDATION
* @todo Find the RFC that describes valid email addresses and post a link to it here.
*/
class EmailRule extends DomainRule {

	/**
	* Constructs a EmailRule
	* @param string fieldname to validate
	* @param array of acceptable values
	* @access public
	*/
	function EmailRule($fieldname) {
		parent :: DomainRule($fieldname);
	}

	/**
	* Performs validation of an email user
	* @param string value to validate
	* @access protected
	* @return void
	* @TODO Verify that this is reasonable:
	*/
    function CheckUser($value) {
        if (!preg_match('/^[a-z0-9]+([_.-][a-z0-9]+)*$/i', $value)) {
            $this->Error('EMAIL_INVALID_USER');
        }
    }

	/**
	* Performs validation of an email domain
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function CheckDomain($value) {
        parent::Check($value);
    }

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (is_integer(strpos($value, '@'))) {
            list($user, $domain) = split('@', $value, 2);
            $this->CheckUser($user);
            $this->CheckDomain($domain);
        } else {
            $this->Error('EMAIL_INVALID');
        }
    }
}

/**
* Check for a valid email address and verify that a mail server
* DNS record exists for this address.
* @see http://wact.sourceforge.net/index.php/DNSEmailRule
* @access public
* @package WACT_VALIDATION
*/
class DNSEmailRule extends EmailRule {

	/**
	* Constructs a DNSEmailRule
	* @param string fieldname to validate
	* @param array of acceptable values
	* @access public
	*/
	function DNSEmailRule($fieldname) {
		parent :: EmailRule($fieldname);
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function CheckDomain($value) {
        parent::CheckDomain($value);
        if ($this->IsValid()) {
            if (!checkdnsrr($value, "MX")) {
                $this->Error('EMAIL_DNS');
            }
        }
    }
}

?>