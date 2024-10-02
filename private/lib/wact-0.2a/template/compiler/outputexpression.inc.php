<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: outputexpression.inc.php,v 1.3 2004/06/15 15:51:51 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Outputs the resultof an expression
* @see CoreOutputTag
* @see http://wact.sourceforge.net/index.php/CoreOutputTag
* @access protected
* @package WACT_TAG
*/
class OutputExpression extends CompilerComponent {
	/**
	* @var Expression
	* @access private
	*/
	var $expression;

	/**
	*/
	function OutputExpression($expression) {
		$this->expression =& new Expression($expression, $this, 'html');
	}

	function prepare() {
	    $this->expression->prepare();
		parent::prepare();
	}
	
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generate(&$code) {
	    if ($this->expression->isConstant()) {
	        $code->writeHTML($this->expression->getValue());
	    } else {
            $this->expression->generatePreStatement($code);
            $code->writePHP('echo ');
            $this->expression->generateExpression($code);
            $code->writePHP(';');		
            $this->expression->generatePostStatement($code);
        }
	}

}
?>