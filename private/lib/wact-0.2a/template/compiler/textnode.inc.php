<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: textnode.inc.php,v 1.12 2004/11/12 21:25:06 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Make sure CompilerDirectiveTag is included
*/
require_once WACT_ROOT . 'template/compiler/compilerdirective.inc.php';
/**
* Used to write literal text from the source template to the compiled
* template
* @see http://wact.sourceforge.net/index.php/TextNode
* @access public
* @package WACT_TEMPLATE
*/
class TextNode extends CompilerDirectiveTag {
	/**
	* A text string to write
	* @var string
	* @access private
	*/
	var $contents;

	/**
	* Constructs TextNode
	* @param string contents of the text node
	* @access protected
	*/
	function TextNode($text) {
		$this->contents = $text;
	}

	/**
	* Appends a further string to the text node
	* @param string
	* @return void
	* @access protected
	*/
	function append($text) {
		$this->contents .= $text;
	}

	/**
	* Writes the contents of the text node to the compiled template
	* using the writeHTML method
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generate(&$code) {
		$code->writeHTML($this->contents);
	}
}

?>