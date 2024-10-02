<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: property.inc.php,v 1.1 2004/05/31 22:18:50 jeffmoore Exp $
*/

class CompilerProperty {

	/**
	* Calcluated values are considered active if they have been referenced
	* in the template.
	* @access private
	*/
    var $isActive = FALSE;

	/**
	* Does this property refer to a constant value at compile time?
	* @return Boolean
	* @access public
	*/
	function isConstant() {
	    return FALSE;
    }
    
    /*
    * @return Boolean Activation status of this property
    * @access public
    */
    function isActive() {
        return $this->isActive;
    }

    /*
    * Indicated that this property is active
    * @access public
    */    
    function activate() {
        $this->isActive = TRUE;
    }

	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
    }

	/**
	* Generate setup code when a property enters a scope in which it is
	* valid.  This is only called if the Property is considered active.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateScopeEntry(&$code) {
    }
    
	/**
	* Generate setup code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePreStatement(&$code) {
    }

	/**
	* Generate the code to read the data value at run time
	* Must generate only a valid PHP Expression.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateExpression(&$code) {
    }
    
	/**
	* Generate tear down code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePostStatement(&$code) {
    }

	/**
	* Generate tear down code when a property enters a scope in which it is
	* valid.  This is only called if the Property is considered active.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateScopeExit(&$code) {
    }

}

?>