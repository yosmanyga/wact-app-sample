<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: default.filter.php,v 1.3 2004/11/18 04:22:47 jeffmoore Exp $
*/

FilterDictionary::registerFilter(
    new FilterInfo('default', 'DefaultFilter', 1, 1), __FILE__);

class DefaultFilter extends CompilerFilter {

	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
	    if ($this->isConstant()) {
	        $value = $this->base->getValue();
	        if (empty($value) && $value !== "0" && $value !== 0) {
	            return $this->parameters[0]->getValue();
	        } else {
	            return $value;
	        }
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
	    $code->registerInclude(WACT_ROOT . 'template/components/core/default_filter.inc.php');
	    $code->writePHP('ApplyDefault(');
	    $this->base->generateExpression($code);
	    $code->writePHP(',');
        $this->parameters[0]->generateExpression($code);
	    $code->writePHP(')');
    }

}

?>