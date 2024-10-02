<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: text.filter.php,v 1.6 2004/11/18 04:22:47 jeffmoore Exp $
*/

FilterDictionary::registerFilter(
    new FilterInfo('text', 'TextFilter', 0, 0), __FILE__);

class TextFilter extends CompilerFilter {

	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
	    if ($this->isConstant()) {
	        return nl2br($this->base->getValue());
	    } else {
            RaiseError('compiler', 'UNRESOLVED_BINDING');
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
	    $code->writePHP('nl2br(');
	    $this->base->generateExpression($code);
	    $code->writePHP(')');
    }

}

?>