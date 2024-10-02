<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: errorlist.inc.php,v 1.2 2004/07/22 15:59:24 harryf Exp $
*/

/**
* Class representing a validation error, used to populate runtime
* components. Handles a single error type but can apply to many
* fields being validated
* @see http://wact.sourceforge.net/index.php/ErrorMessageCode
* @access public
* @package WACT_VALIDATION
*/
class ErrorMessageCode /* Extends Error? */ {
    /**
    * Instance of ErrorList
    * @var array
    * @access private
    */
    var $Parent;

    /**
    * @var string
    * @access private
    */
    var $Group;
    
    /**
    * @var string
    * @access private
    */
    var $Id;
    
    /**
    * @var array
    * @access private
    */
    var $FieldList;
    
    /**
    * @var array
    * @access private
    */
    var $Values;
    
    /**
    * Returns the Error message for this error
    * @return string
    * @access public
    */
    function getErrorMessage() {
        $this->Parent->ensureErrorMessageDictionary();
        $text = $this->Parent->ErrorMessageDictionary->getMessage($this->Group, $this->Id);

        if (count($this->FieldList) > 0) {
            $this->Parent->ensureFieldNameDictionary();
        }        
        foreach($this->FieldList as $key => $fieldName) {
            $replacement = $this->Parent->FieldNameDictionary->getFieldName($fieldName);
            $text = str_replace('{' . $key . '}', $replacement, $text);
        }
        
        foreach($this->Values as $key => $replacement) {
            $text = str_replace('{' . $key . '}', $replacement, $text);
        }

        return $text;
    }
}

class ErrorMessageText {
    /**
    * @var string
    * @access private
    */
    var $Message;
    
    /**
    * @var array
    * @access private
    */
    var $FieldList;

    /**
    * Returns the Error message for this error
    * @return string
    * @access public
    */
    function getErrorMessage() {
        return $this->Message;
    }    
}

/**
* Container for errors implementing the Iterator iterface
* @todo documention - check that ErrObj is ErrorMessageCode
* @see http://wact.sourceforge.net/index.php/ErrorList
* @see http://wact.sourceforge.net/index.php/Iterator
* @access public
* @package WACT_VALIDATION
*/
class ErrorList {
    /**
    * Full list of ErrorMessage objects
    * @var array
    * @access private
    */
    var $errors = array();
    /**
    * Switch for when at start of iteration
    * @var boolean
    * @access private
    */
    var $first = TRUE;
    /**
    * The current ErrorMessage from the array
    * @var ErrorMessage
    * @access private
    */
    var $currentError;
    /**
    * The fields that should be returned by the ErrorList
    * @var array
    * @access private
    */
    var $fieldRestriction;    

    /**
    * Set the ErrorMessageDictionary
    * @see ErrorMessageDictionary
    * @param ErrorMessageDictionary
    * @return void
    * @access public
    */
    function setErrorMessageDictionary(&$Dictionary) {
        $this->ErrorMessageDictionary =& $Dictionary;
    }

    /**
    * Ensures that an error message dictionary is available, creating
    * a default dictionary if one is not set
    * @see ErrorMessageDictionary
    * @return void
    * @access public
    */
    function ensureErrorMessageDictionary() {
        if (!isset($this->ErrorMessageDictionary)) {
            require_once WACT_ROOT . 'validation/messagedictionary.inc.php';
            $this->setErrorMessageDictionary(new ErrorMessageDictionary());
        }
    }

    /**
    * Set the FieldNameDictionary
    * @see FieldNameDictionary
    * @param FieldNameDictionary
    * @return void
    * @access public
    */
    function setFieldNameDictionary(&$Dictionary) {
        $this->FieldNameDictionary =& $Dictionary;
    }

    /**
    * Ensures that an error message dictionary is available, creating
    * a default dictionary if one is not set
    * @see ErrorMessageDictionary
    * @return void
    * @access public
    */
    function ensureFieldNameDictionary() {
        if (!isset($this->FieldNameDictionary)) {
            require_once WACT_ROOT . 'validation/fielddictionary.inc.php';
            $this->setFieldNameDictionary(new DefaultFieldNameDictionary());
        }
    }
    
    /**
    * Add an error code to the error list.
    * @return void
    * @access public
    */
    function addError($Group, $Id, $FieldList = NULL, $Values = NULL) {
        $Error =& new ErrorMessageCode();

        $Error->Parent =& $this;
        $Error->Group = $Group;
        $Error->Id = $Id;
        $Error->FieldList = empty($FieldList) ? array() : $FieldList;
        $Error->Values = empty($Values) ? array() : $Values;
        
        $this->errors[] =& $Error;
    }
    
    /**
    * Add an error message to the error list.
    * @return void
    * @access public
    */
    function addErrorMessage($Message, $FieldList = NULL) {
        $Error =& new ErrorMessageText();
        $Error->Message = $Message;
        $Error->FieldList = empty($FieldList) ? array() : $FieldList;
        $this->errors[] =& $Error;
    }

    /**
    * Iterator method
    * @return void
    * @access public
    */
    function reset() {
        $this->first = TRUE;
    }

    /**
    * Iterator method
    * @return boolean TRUE is more errors
    * @access public
    */
    function next() {
        if ($this->first) {
            $result = reset($this->errors);
            $this->first = FALSE;
        } else {
            $result = next($this->errors);
        }
        if ($result === FALSE) {
            return FALSE;
        } else {
            $this->currentError =& $this->errors[key($this->errors)];
            if (isset($this->fieldRestriction)) {
                if (count(array_intersect($this->fieldRestriction, $this->currentError->FieldList)) > 0) {
                    return TRUE;
                } else {
                    return $this->next();
                }
            }
            return TRUE;
        }
    }

    /**
    * Returns the current ValidationError
    * @return ValidationError
    * @access public
    */
    function &getError() {
        return $this->currentError;
    }

    /**
    * Gets an error message from the current ValidationError object
    * @param string name of error
    * @return string error message
    * @access public
    */
    function getMessage() {
        return $this->currentError->getErrorMessage();
    }

    /* backward compatibility */
    function get($name) {
        return $this->currentError->getErrorMessage();
    }

    /**
    * Fields which errors should be applied to ???
    * @param array list of fields
    * @return void
    * @access public
    */
    function restrictFields($fieldRestriction) {
        $this->fieldRestriction = $fieldRestriction;
    }
    
    function removeRestrictions() {
        unset($this->fieldRestriction);
    }

}
