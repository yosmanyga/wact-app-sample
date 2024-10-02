<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_CONTROLLERS
* @version $Id: responsemodel.inc.php,v 1.2 2004/11/14 15:49:51 jeffmoore Exp $
*/
// EXPERIMENTAL

require_once WACT_ROOT . 'util/dataspace.inc.php';

/**
* @access public
* @package WACT_CONTROLLERS
*/
class ResponseModel extends DataSpace {

    var $isValid = TRUE;
    var $errorList = NULL;
    
    function ensureErrorList() {
        if (!is_object($this->errorList)) {
            require_once WACT_ROOT . 'validation/errorlist.inc.php';
            $this->errorList =& new ErrorList();
        }
    }

    function addError($group, $id, $fieldList = NULL, $values = NULL) {
        $this->ensureErrorList();
        $this->errorList->addError($group, $id, $fieldList, $values);
        $this->isValid = FALSE;
    }

    function addErrorMessage($message, $fieldList = NULL) {
        $this->ensureErrorList();
        $this->errorList->addErrorMessage($message, $fieldList);
        $this->isValid = FALSE;
    }
    
    function isValid() {
        return $this->isValid;
    }
    
    function &getErrorList() {
        return $this->errorList;
    }
    
    function applyRule(&$rule) {
        $result = $rule->validate($this, $this);
        $this->isValid = $this->isValid && $result;
        return $result;
    }
        
}

?>