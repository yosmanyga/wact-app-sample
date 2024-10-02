<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: validator.inc.php,v 1.29 2004/11/16 01:55:37 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* Performs the validation checks against the Rules
* @see http://wact.sourceforge.net/index.php/Validator
* @see http://wact.sourceforge.net/index.php/Rule
* @access public
* @package WACT_VALIDATION
*/
class Validator {
    /**
    * Indexed array of Rule objects
    * @see Rule
    * @var array
    * @access private
    */
    var $rules = array();
    /**
    * Instance of ErrorList
    * @see ErrorList
    * @var ErrorList
    * @access private
    */
    var $ErrorList;
    /**
    * Whether the validation process was valid
    * @var boolean
    * @access private
    */
    var $IsValid = TRUE;

    /**
    * Initalize Error List
    * @param Rule
    * @return void
    * @access protected
    */
    function &createErrorList() {
        require_once WACT_ROOT . 'validation/errorlist.inc.php';
        return new ErrorList();
    }

    /**
    * Registers a Rule
    * @param Rule
    * @return void
    * @access public
    */
    function addRule(&$Rule) {
        $this->rules[] = $Rule;
    }

    /**
    * Returns the ErrorList
    * @return ErrorList
    * @access public
    */
    function &getErrorList() {
        return $this->ErrorList;
    }

    /**
    * Whether the validation process was valid
    * @param string fieldname (default=NULL) unused
    * @return boolean TRUE if valid
    * @access public
    */
    function IsValid($FieldName = NULL) {
        return $this->IsValid;
    }

    /**
    * Perform the validation
    * @param DataSpace subclass of DataSpace to validate
    * @return void
    * @access public
    */
    function validate(&$DataSource) {
        $ErrorList =& $this->createErrorList();
        foreach( array_keys($this->rules) as $key) {
            if (! $this->rules[$key]->validate($DataSource, $ErrorList)) {
                $this->IsValid = FALSE;
            }
        }

        $this->ErrorList =& $ErrorList;
    }
}

?>