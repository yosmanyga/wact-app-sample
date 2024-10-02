<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: rowodd.prop.php,v 1.2 2004/11/18 04:22:49 jeffmoore Exp $
*/

PropertyDictionary::registerProperty(
    new PropertyInfo('ListRowOdd', 'list:LIST', 'ListRowOddProperty'), __FILE__);

class ListRowOddProperty extends CompilerProperty {

    var $tempvar;
    var $hasIncrement = FALSE;

	function generateScopeEntry(&$code) {
        $this->tempvar = $code->getTempVariable();
        $code->writePHP('$' . $this->tempvar . ' = 0;');
    }

	/**
	* Generate setup code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePreStatement(&$code) {
	    if (!$this->hasIncrement) {
            $this->hasIncrement = TRUE;
            $code->writePHP('$' . $this->tempvar . '++;');
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
	    $code->writePHP('(Boolean) ( $' . $this->tempvar . ' % 2)');
    }

}
?>