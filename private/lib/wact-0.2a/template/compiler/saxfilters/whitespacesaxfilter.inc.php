<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: whitespacesaxfilter.inc.php,v 1.7 2004/11/12 21:25:07 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Includes
*/
require_once WACT_ROOT . 'template/compiler/saxfilters/basesaxfilter.inc.php';
/**
* SaxFilter for whitespace compression in template. Removes all whitespace
* except that inside a pre tag
* @see http://wact.sourceforge.net/index.php/WhitespaceSaxFilter
* @access public
* @package WACT_TEMPLATE
* @abstract
*/
class WhitespaceSaxFilter extends BaseSaxFilter {

	/**
	* Whether we're inside an HTML page
	* @var boolean (default = FALSE)
	* @access private
	*/
	var $inHtml = FALSE;

	/**
	* Whether we're inside an HTML where the contents
	* are preformatted e.g. pre or script
	* @var boolean (default = FALSE)
	* @access private
	*/
	var $inPre = FALSE;
	
	/**
	* @param string tag name
	* @param array attributes
	* @return void
	* @access private
	*/
	function startElement($tag, $attrs) {
		switch ( strtolower($tag) ) {
			case 'textarea':
			case 'script':
			case 'pre':
				$this->inPre = TRUE;
			break;
			case 'html':
				$this->inHtml = TRUE;
			break;
		}
		parent::startElement($tag, $attrs);
	}

	/**
	* @param string tag name
	* @return void
	* @access private
	*/
	function endElement($tag) {
		switch ( strtolower($tag) ) {
			case 'textarea':
			case 'script':
			case 'pre':
				$this->inPre = FALSE;
			break;
			case 'html':
				$this->inHtml = FALSE;
			break;
		}
		parent::endElement($Parser, $tag, $empty);
	}

	/**
	* @param string text content in tag
	* @return void
	* @access private
	*/
	function characters($text) {
		if ( !$this->inPre && $this->inHtml ) {
			$text = trim($text);
			$text = preg_replace('/\s+/', ' ', $text);
		}
		parent::characters($Parser, $text);
	}
}
?>