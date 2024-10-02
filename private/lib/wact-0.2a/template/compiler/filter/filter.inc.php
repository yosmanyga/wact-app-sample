<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: filter.inc.php,v 1.1 2004/06/02 22:18:13 jeffmoore Exp $
*/

class CompilerFilter {

    var $base;
    var $parameters = array();

    function registerBase(&$base) {
        $this->base =& $base;
    }
    
    function registerParameter(&$parameter) {
        $this->parameters[] =& $parameter;
    }

	/**
	* Does this filter refer to a constant value at compile time?
	* @return Boolean
	* @access public
	*/
	function isConstant() {
	    $isConstant = $this->base->isConstant();
		foreach( array_keys($this->parameters) as $key) {
			$isConstant = $isConstant && $this->parameters[$key]->isConstant();
		}
	    return $isConstant;
    }
    
	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
    }

	/**
	* Generate setup code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePreStatement(&$code) {
	    $this->base->generatePreStatement($code);
		foreach( array_keys($this->parameters) as $key) {
			$this->parameters[$key]->generatePreStatement($code);;
		}
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
	    $this->base->generatePostStatement($code);
		foreach( array_keys($this->parameters) as $key) {
			$this->parameters[$key]->generatePostStatement($code);;
		}
    }

	function prepare() {
	    $this->base->prepare();
		foreach( array_keys($this->parameters) as $key) {
			$this->parameters[$key]->prepare();;
		}
    }

}

?>