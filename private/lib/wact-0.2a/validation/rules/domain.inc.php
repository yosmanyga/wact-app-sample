<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: domain.inc.php,v 1.7 2004/11/16 01:55:37 jeffmoore Exp $
*/

require_once WACT_ROOT . 'validation/rule.inc.php';

/**
* Check for a valid domain name.
* @see http://wact.sourceforge.net/index.php/DomainRule
* @access public
* @package WACT_VALIDATION
*/
class DomainRule extends SingleFieldRule {

	/**
	* Constructs a DomainRule
	* @param string fieldname to validate
	* @param array of acceptable values
	* @access public
	*/
	function DomainRule($fieldname) {
		parent :: SingleFieldRule($fieldname);
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	* @todo Find the RFC that describes valid domain names and post a link to it here.
	*/
    function Check($value) {
        // Check for entirely numberic domains.  Is 666.com valid?
        // Don't check for 2-4 character length on TLD because of things like .local
        // We can't be too restrictive by default.
        if (!preg_match("/^[a-z0-9.-]+$/i", $value)) {
            $this->Error('BAD_DOMAIN_CHARACTERS');
        }

        if (is_integer(strpos($value, '--', $value))) {
            $this->Error('BAD_DOMAIN_DOUBLE_HYPHENS');
        }

        if (0 === strpos($value, '.')) {
            $this->Error('BAD_DOMAIN_STARTING_PERIOD');
        }

        if (strlen($value) -1 === strrpos($value, '.')) {
            $this->Error('BAD_DOMAIN_ENDING_PERIOD');
        }

        if (is_integer(strpos($value, '..', $value))) {
            $this->Error('BAD_DOMAIN_DOUBLE_DOTS');
        }

        $segments = explode('.', $value);
        foreach($segments as $dseg) {
            $len = strlen($dseg);
            /* ignore empty segments that're due to other errors */
            if (1 > $len) {
                continue;
            }
            if ($len > 63) {
                $this->Error('BAD_DOMAIN_SEGMENT_TOO_LARGE',
                             array('segment' => $dseg));
            }
            if ($dseg{$len-1} == '-' || $dseg{0} == '-') {
                $this->Error('BAD_DOMAIN_HYPHENS', array('segment' => $dseg));
            }

        }
	}
}

/**
* check for a valid domain name with a valid DNS Record.
* If DNS is down, data will not be considered invalid,
* possibly preventing data entry when connectivity is bad.
* @see http://wact.sourceforge.net/index.php/DNSDomainRule
* @access public
* @package WACT_VALIDATION
*/
class DNSDomainRule extends DomainRule {

	/**
	* Constructs a DNSDomainRule
	* @param string fieldname to validate
	* @param array of acceptable values
	* @access public
	*/
	function DNSDomainRule($fieldname) {
		parent :: SingleFieldRule($fieldname);
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {

        parent::Check($value);
        if ($this->IsValid()) {
            if (!checkdnsrr($value, 'A')) {
                $this->Error('BAD_DOMAIN_DNS');
            }
        }
	}
}
?>