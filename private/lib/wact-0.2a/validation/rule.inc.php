<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: rule.inc.php,v 1.1 2004/11/16 01:55:37 jeffmoore Exp $
*/

/**
* Base class for defining Rules to validate against
* @see http://wact.sourceforge.net/index.php/Rule
* @access public
* @package WACT_VALIDATION
* @abstract
*/
class Rule {
    /**
    * Identifies error message group in vars file
    * @var string (default='validation')
    * @access private
    */
    var $Group = 'validation';
    
    /**
    * Sets the error message group (related to the vars file)
    * @param string group
    * @return void
    * @access public
    */
    function setGroup($Group) {
        $this->Group = $Group;
    }

    /**
    * Perform validation
    * @param DataSource - subclass to validate
    * @param ErrorList
    * @return boolean (always TRUE is base class)
    * @access protected
    * @abstract
    */
    function validate(&$DataSource, &$ErrorList) {
        RaiseError('compiler', 'ABSTRACTMETHOD',
                   array('method' => __FUNCTION__ .'()', 'class' => __CLASS__));
    }
}

/**
* Rules responsbile for validating a single field descend from this class.
* @see http://wact.sourceforge.net/index.php/SingleFieldRule
* @access public
* @package WACT_VALIDATION
* @abstract
*/
class SingleFieldRule extends Rule {
    /**
    * Field name to validate
    * @var string
    * @access private
    */
    var $fieldname;
    /**
    * Is this field valid?
    * @var boolean
    * @access private
    */
    var $IsValid = TRUE;
    /**
    * Error Collection Object
    * @var ErrorList
    * @access private
    */
    var $ErrorList;

    /**
    * Constructs Rule
    * @param string fieldname to validate
    * @access public
    */
    function SingleFieldRule($fieldname) {
        $this->fieldname = $fieldname;
    }

    /**
    * Returns the fieldname the rule applies to
    * @return string name of field
    * @access public
    */
    function getField() {
        return $this->fieldname;
    }

    /**
    * Signal that an error has occurred.
    * @param string id of the error
    * @param optional data regarding the error
    * @access protected
    */
    function Error($id, $values = NULL) {
        $this->IsValid = FALSE;
        $this->ErrorList->addError($this->Group, $id, 
            array('Field' => $this->fieldname), $values);
    }

    /**
    * Have we already determined this error to be invalid?
    * @param string id of the error
    * @param optional data regarding the error
    * @access protected
    */
    function IsValid() {
        return $this->IsValid;
    }
    
    /**
    * Perform validation
    * @param DataSource - Data to validate
    * @param ErrorList
    * @return boolean (always TRUE is base class)
    * @access public
    */
    function validate(&$DataSource, &$ErrorList) {
        $this->IsValid = TRUE;
        $this->ErrorList =& $ErrorList;
        $value = $DataSource->get($this->fieldname);
        if (isset($value) && $value !== '') {
            $this->Check($value);
        }
        return $this->IsValid;
    }

    /**
    * Check a Single Value to see if its valid
    * @param value - to check
    * @access protected
    * @abstract
    */
    function Check($value) {
        RaiseError('compiler', 'ABSTRACTMETHOD',
                   array('method' => __FUNCTION__ .'()', 'class' => __CLASS__));
    }
}

/**
* For fields which must be supplied a value by the user
* @see http://wact.sourceforge.net/index.php/RequiredRule
* @access public
* @package WACT_VALIDATION
*/
class RequiredRule extends SingleFieldRule {
    /**
    * Constructs RequiredRule
    * @param string fieldname to validate
    * @access public
    */
    function RequiredRule($fieldname) {
        parent :: SingleFieldRule($fieldname);
    }

	/**
	* Performs validation
	* @param DataSource - data to validate
	* @param ErrorList
	* @return boolean TRUE if validation passed
	* @access public
	*/
    function validate(&$DataSource, &$ErrorList) {
        $value = $DataSource->get($this->fieldname);
        if (!isset($value) || $value === '') {
            $ErrorList->addError($this->Group, 'MISSING', 
                array('Field' => $this->fieldname));
            return FALSE;
        }
        return TRUE;
    }
}

/**
* For fields have a minimum and maximum length
* @see http://wact.sourceforge.net/index.php/SizeRangeRule
* @access public
* @package WACT_VALIDATION
*/
class SizeRangeRule extends SingleFieldRule {
    /**
    * Minumum length
    * @var int
    * @access private
    */
    var $minLength;
    /**
    * Maximum length
    * @var int
    * @access private
    */
    var $maxLength;

    /**
    * Constructs SizeRangeRule
    * @param string fieldname to validate
    * @param int Minumum length
    * @param int Maximum length (optional)
    * @access public
    */
    function SizeRangeRule($fieldname, $minLength, $maxLength = NULL) {
        parent :: SingleFieldRule($fieldname);
        if (is_null($maxLength)) {
            $this->minLength = NULL;
            $this->maxLength = $minLength;
        } else {
            $this->minLength = $minLength;
            $this->maxLength = $maxLength;
        }
    }

	/**
	* Performs validation of a single value
	* @access protected
	*/
    function Check($value) {
        if (!is_null($this->minLength) && (strlen($value) < $this->minLength)) {
            $this->Error('SIZE_TOO_SMALL', array('min' => $this->minLength));
        } else if (strlen($value) > $this->maxLength) {
            $this->Error('SIZE_TOO_BIG', array('max' => $this->maxLength));
        }
    }
}

?>