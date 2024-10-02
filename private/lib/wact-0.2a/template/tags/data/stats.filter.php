<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @author	Jason E. Sweat < jsweat_php AT yahoo DOT com >
* @version $Id: stats.filter.php,v 1.6 2004/11/18 04:22:48 jeffmoore Exp $
*/

FilterDictionary::registerFilter(
    new FilterInfo('stats', 'StatsFilter', 1, 2), __FILE__);


class StatsFilter extends CompilerFilter {

	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
	    if ($this->isConstant()) {
			$value = $this->base->getValue();
			$id = $this->parameters[0]->getValue();
			if (array_key_exists(1,$this->parameters)) {
				$mode = $this->parameters[1]->getValue();
				$code->writeHTML(wact_stats_filter($value, $id, $mode));
			} else {
				$code->writeHTML(wact_stats_filter($value, $id));
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
	    $code->registerInclude(WACT_ROOT . 'template/components/data/stats_filter.inc.php');
	    $code->writePHP('wact_stats_filter(');
	    $this->base->generateExpression($code);
	    $code->writePHP(',');
        $this->parameters[0]->generateExpression($code);
        if (array_key_exists(1,$this->parameters)) {
			$code->writePHP(',');
			$this->parameters[1]->generateExpression($code);
        }
	    $code->writePHP(')');
    }

}

?>