<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_FILTER
* @author	Jason E. Sweat < jsweat_php AT yahoo DOT com >
* @version $Id: math.filter.php,v 1.3 2004/11/18 04:22:48 jeffmoore Exp $
*/

FilterDictionary::registerFilter(
    new FilterInfo('math', 'MathFilter', 1, 1), __FILE__);

class MathFilter extends CompilerFilter {

	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
	    if ($this->isConstant()) {
			$value = $this->base->getValue();
			$exp = $this->parameters[0]->getValue();
			$code->writeHTML(math_filter($value, $exp));
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
	    $code->registerInclude(WACT_ROOT . 'template/components/data/math_filter.inc.php');
	    $code->writePHP('math_filter(');
		$this->base->generateExpression($code);
	    $code->writePHP(',');
        $this->parameters[0]->generateExpression($code);
	    $code->writePHP(',');
	    $code->writePHPLiteral($this->base->context->SourceFile);
	    $code->writePHP(',');
	    $code->writePHPLiteral($this->base->context->StartingLineNo);
	    $code->writePHP(')');
    }

}

?>