<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: constant.inc.php,v 1.2 2004/06/04 19:45:07 jeffmoore Exp $
*/

/**
* A property representing a constant value.
* @access public
* @package WACT_TEMPLATE
*/
class ConstantProperty extends CompilerProperty {
    
    var $value;
    
    function ConstantProperty($value) {
        $this->value =& $value;
    }

	/**
	* Does this property refer to a constant value at compile time?
	* @return Boolean
	* @access public
	*/
	function isConstant() {
	    return TRUE;
    }

	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
	    return $this->value;
    }

	/**
	* Generate the code to read the data value at run time
	* Must generate only a valid PHP Expression.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateExpression(&$code) {
        $code->writePHPLiteral($this->value);
    }
    
    function prepare() {
    }
}

?>