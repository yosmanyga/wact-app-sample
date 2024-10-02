<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: wordwrap.filter.php,v 1.3 2004/11/18 04:22:47 jeffmoore Exp $
*/

FilterDictionary::registerFilter(
    new FilterInfo('wordwrap', 'WordWrapFilter', 1, 1), __FILE__);

class WordWrapFilter extends CompilerFilter {

	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
	    if ($this->isConstant()) {
	        return wordwrap($this->base->getValue(), $this->parameters[0]->getValue(), "\n", TRUE);
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
	    $code->writePHP('wordwrap(');
	    $this->base->generateExpression($code);
	    $code->writePHP(',');
	    $this->parameters[0]->generateExpression($code);
	    $code->writePHP(', "\n", TRUE)');
    }

}

?>