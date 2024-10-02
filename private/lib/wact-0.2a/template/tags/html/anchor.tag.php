<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: anchor.tag.php,v 1.7 2004/11/12 21:25:10 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Load parent
*/
require_once WACT_ROOT . 'template/tags/html/base.tag.php';
/**
* Compile time component for HTML Anchors
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/HtmlAnchorTag
* @access protected
* @package WACT_TAG
*/
class HtmlAnchorTag extends HtmlBaseTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = 'template/components/html/html_anchor.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'HtmlAnchorComponent';
	/**
	* Constructs HtmlAnchorTag
	* @access protected
	*/
	function HtmlAnchorTag() {
		parent::HtmlBaseTag();
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$code->writeHTML('<a');
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		if ( $href = $this->getAttribute('href') ) {
			$code->writeHTML(' href="'.$href.'"');
		}
		parent::generateContents($code);
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$code->writeHTML("</a>\n");
		parent::postGenerate($code);
	}
}
?>