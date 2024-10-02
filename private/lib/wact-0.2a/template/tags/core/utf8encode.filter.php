<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: utf8encode.filter.php,v 1.2 2004/11/18 04:22:47 jeffmoore Exp $
*/

FilterDictionary::registerFilter(
    new FilterInfo('utf8encode', 'Utf8EncodeFilter', 0, 0), __FILE__);

/**
* @see http://www.php.net/utf8_encode
* @see http://wact.sourceforge.net/index.php/Utf8EncodeFilter
* @package WACT_TAG
* @access protected
*/
class Utf8EncodeFilter extends CompilerFilter {

	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
	    if ($this->isConstant()) {
	        return utf8_encode($this->base->getValue());
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
	    $code->writePHP('utf8_encode(');
	    $this->base->generateExpression($code);
	    $code->writePHP(')');
    }

}

?>