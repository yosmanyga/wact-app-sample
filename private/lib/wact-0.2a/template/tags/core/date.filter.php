<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_FILTER
* @author	Jason E. Sweat < jsweat_php AT yahoo DOT com >
* @version $Id: date.filter.php,v 1.4 2004/11/18 04:22:47 jeffmoore Exp $
*/

FilterDictionary::registerFilter(
    new FilterInfo('date', 'DateFilter', 0, 1), __FILE__);
FilterDictionary::registerFilter(
    new FilterInfo('todate', 'ToDateFilter', 0, 1), __FILE__);

class DateFilter extends CompilerFilter {

	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
	    if ($this->isConstant()) {
			$value = $this->base->getValue();
			$exp = $this->parameters[0]->getValue();
			//return date($exp, $value);
			return strftime($exp, $value);
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
	    //$code->registerInclude(WACT_ROOT . 'template/components/core/date_filter.inc.php');
	    //$code->writePHP('date(');
	    $code->writePHP('strftime(');		
        $this->parameters[0]->generateExpression($code);
	    $code->writePHP(',');
		$this->base->generateExpression($code);
	    $code->writePHP(')');
    }

}

class ToDateFilter extends CompilerFilter {
	var $input;
	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
	    if ($this->isConstant()) {
			if ($value = $this->base->getValue()) {
				return strtotime($value);
			}
	    } else {
            RaiseError('compiler', 'UNRESOLVED_BINDING');
	    }
    }

	/**
	* Generate setup code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePreStatement(&$code) {
		parent::generatePreStatement($code);
		$this->input = $code->getTempVarRef();
		$code->writePHP($this->input.'=');
		$this->base->generateExpression($code);
		$code->writePHP(';');
		//$code->writePHP($this->input.'=('.$this->input.')?'.$this->input.':mktime();');
	}
	/**
	* Generate the code to read the data value at run time
	* Must generate only a valid PHP Expression.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateExpression(&$code) {
	    //$code->registerInclude(WACT_ROOT . 'template/components/core/date_filter.inc.php');
	    $code->writePHP('(('.$this->input.')?strtotime('.$this->input.'):"")');
    }

}

?>