<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: attribute.inc.php,v 1.3 2004/05/31 21:57:06 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* A property linked to the value of an attribute.
* @access public
* @package WACT_TEMPLATE
*/
class AttributeProperty extends CompilerProperty {

    var $attribute;

    function AttributeProperty(&$attribute) {
        $this->attribute =& $attribute;
    }

	/**
	* Does this property refer to a constant value at compile time?
	* @return Boolean
	* @access public
	*/
	function isConstant() {
	    return $this->attribute->isConstant();
    }

	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
        return $this->attribute->getValue();
    }

	/**
	* Generate setup code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePreStatement(&$code) {
        $this->attribute->generatePreStatement($code);
    }

	/**
	* Generate the code to read the data value at run time
	* Must generate only a valid PHP Expression.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateExpression(&$code) {
        $this->attribute->generateExpression($code);
    }

	/**
	* Generate tear down code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePostStatement(&$code) {
        $this->attribute->generatePostStatement($code);
    }

}

?>