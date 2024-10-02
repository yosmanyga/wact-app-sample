<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: basesaxfilter.inc.php,v 1.18 2004/07/25 18:50:47 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* BaseSaxFilter - handler methods are declared so concrete SaxFilters
* do not need to explicity declare them when not required.
* @see http://wact.sourceforge.net/index.php/BaseSaxFilter
* @access public
* @package WACT_TEMPLATE
* @abstract
*/
class BaseSaxFilter {

	/**
	* Child Sax filter
	* @var object subclass of BaseSaxFilter
	* @access public
	*/
	var $ChildSaxFilter;

	/**
	* @param BaseSaxFilter Next Filter in the Chain
	* @return void
	* @access public
	*/
	function setChildSaxFilter(&$SaxFilter) {
		$this->ChildSaxFilter =& $SaxFilter;
	}

	/**
	* @param Locator interface for determining location in source file
	* @return void
	* @access public
	*/
	function setDocumentLocator(&$locator) {
		$this->ChildSaxFilter->setDocumentLocator($locator);
	}

	/**
	* Sax Open Handler
	* @param string tag name
	* @param array attributes
	* @return void
	* @access private
	*/
	function startElement($tag, $attrs) {
		$this->ChildSaxFilter->startElement($tag, $attrs);
	}

	/**
	* Sax Close Handler
	* @param string tag name
	* @return void
	* @access private
	*/
	function endElement($tag) {
		$this->ChildSaxFilter->endElement($tag);
	}

	/**
	* Sax Open Handler
	* @param string tag name
	* @param array attributes
	* @return void
	* @access private
	*/
	function emptyElement($tag, $attrs) {
		$this->ChildSaxFilter->emptyElement($tag, $attrs);
	}

	/**
	* Sax Data Handler
	* @param string text content in tag
	* @return void
	* @access private
	*/
	function characters($text) {
		$this->ChildSaxFilter->characters($text);
	}

	/**
	* Sax cdata Handler
	* @param string text content in tag
	* @return void
	* @access private
	*/
	function cdata($text) {
		$this->ChildSaxFilter->cdata($text);
	}

	/**
	* Sax Processing Instruction Handler
	* @param string target processor (e.g. php)
	* @param string text content in PI
	* @return void
	* @access private
	*/
	function processingInstruction($target, $instruction) {
		$this->ChildSaxFilter->processingInstruction($target, $instruction);
	}

	/**
	* Sax XML Escape Handler
	* @param string text content in escape
	* @return void
	* @access private
	*/
	function escape($text) {
		$this->ChildSaxFilter->escape($text);
	}

	/**
	* Sax XML Comment Handler
	* @param string text content in comment
	* @return void
	* @access private
	*/
	function comment($text) {
		$this->ChildSaxFilter->comment($text);
	}

	/**
	* Sax doctype Handler
	* @param string text content in doctype
	* @return void
	* @access private
	*/
	function doctype($text) {
		$this->ChildSaxFilter->doctype($text);
	}

	/**
	* Sax XML Jasp Handler
	* @param string text content in JASP block
	* @return void
	* @access private
	*/
	function jasp($text) {
		$this->ChildSaxFilter->jasp($text);
	}
	
	/**
	* Sax EOF Handler
	* @param string text content in tag
	* @return void
	* @access private
	*/
	function unexpectedEOF($text) {
		$this->ChildSaxFilter->unexpectedEOF($text);
	}

	/**
	* Sax Entity syntax Error Handler
	* @param string text content in tag
	* @return void
	* @access private
	*/
	function invalidEntitySyntax($text) {
		$this->ChildSaxFilter->invalidEntitySyntax($text);
	}

	/**
	* Sax Attribute syntax Error Handler
	* @param string text content in tag
	* @return void
	* @access private
	*/
	function invalidAttributeSyntax() {
		$this->ChildSaxFilter->invalidAttributeSyntax();
	}
	
}

?>