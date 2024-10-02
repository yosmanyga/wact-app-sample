<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: phpnode.inc.php,v 1.3 2004/11/12 21:25:06 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Make sure CompilerDirectiveTag is included
*/
require_once WACT_ROOT . 'template/compiler/compilerdirective.inc.php';
/**
* Used to write literal PHP. Current sole purpose is for handling XML processing
* instructions in a template.
* @see http://wact.sourceforge.net/index.php/PHPNode
* @access public
* @package WACT_TEMPLATE
*/
class PHPNode extends CompilerDirectiveTag {
	/**
	* A PHP string to write
	* @var string
	* @access private
	*/
	var $contents;

	/**
	* Constructs PHPNode
	* @param string contents of the PHP node
	* @access protected
	*/
	function PHPNode($text) {
		$this->contents = $text;
	}

	/**
	* Writes the contents of the PHP node to the compiled template
	* using the writePHP method
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generate(&$code) {
		$code->writePHP($this->contents);
	}
}
?>