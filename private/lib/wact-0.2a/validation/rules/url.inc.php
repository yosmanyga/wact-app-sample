<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: url.inc.php,v 1.4 2004/11/16 01:55:38 jeffmoore Exp $
*/

/**
* Include domain rule
*/
require_once 'domain.inc.php';

/**
* Check for a valid Url.
* @see http://wact.sourceforge.net/index.php/UrlRule
* @access public
* @package WACT_VALIDATION
*/
class UrlRule extends DomainRule {
    /**
    * Array of allowable URL schemes e.g. http, ftp, https etc.
    * @var array
    * @access private
    */
    var $AllowableSchemes;
    
	/**
	* Constructs a DomainRule
	* @param string fieldname to validate
	* @param array of acceptable URL schemes e.g. http, ftp, https etc.
	* @access public
	*/
	function UrlRule($fieldname, $AllowableSchemes = NULL) {
		parent :: SingleFieldRule($fieldname);
		$this->AllowableSchemes = $AllowableSchemes;
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        $url = parse_url($value);
        if (isset($url['scheme']) && isset($url['host']) &&
            ($url['scheme'] == 'http' || $url['scheme'] == 'ftp')) {
            parent::check($url['host']);
        }
        if (!is_null($this->AllowableSchemes)) {
            if (isset($url['scheme'])) {
                if (!in_array($url['scheme'], $this->AllowableSchemes)) {
                    $this->Error('URL_BAD_SCHEME', array('scheme' => $url['scheme']));
                }
            } else {
                // It would be nice here to tell them which schemes would work, but a way to
                // do this in an internationalizable way escapes me for now.
                $this->Error('URL_MISSING_SCHEME');
            }
        }
        // Check that port contains only digits.
    }
}

/*
class UrlExistsRule {

	function UrlExistsRule($fieldname, $statuscodes = array('200')) {
	}

}
*/
?>