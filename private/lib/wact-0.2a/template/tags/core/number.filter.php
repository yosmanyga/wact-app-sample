<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: number.filter.php,v 1.2 2004/11/18 04:22:47 jeffmoore Exp $
*/

FilterDictionary::registerFilter(
    new FilterInfo('number', 'NumberFilter', 0, 3), __FILE__);

/**
* @see http://wact.sourceforge.net/index.php/FilterInfoClasses
* @access protected
* @package WACT_TAG
*/
class NumberFilter extends CompilerFilter {

	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
		$places = 0;
		$decimal = '.';
		$thousep = ',';
		if (array_key_exists(0, $this->parameters)) {
			$places = (int)$this->parameters[0]->getValue();
		}
		if (array_key_exists(1, $this->parameters)
			&& array_key_exists(2, $this->parameters)) {
			$decimal = $this->parameters[1]->getValue();
			$thousep = $this->parameters[2]->getValue();
		}
	    if ($this->isConstant()) {
	        return number_format($this->base->getValue(), $places, $decimal, $thousep);
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
	    $code->writePHP('number_format(');
	    $this->base->generateExpression($code);
		if (array_key_exists(0, $this->parameters)) {
			$code->writePHP(',');
			$this->parameters[0]->generateExpression($code);
		}
		if (array_key_exists(1, $this->parameters)
			&& array_key_exists(2, $this->parameters)) {
			$code->writePHP(',');
			$this->parameters[1]->generateExpression($code);
			$code->writePHP(',');
			$this->parameters[2]->generateExpression($code);
		}
	    $code->writePHP(')');
    }

}

?>