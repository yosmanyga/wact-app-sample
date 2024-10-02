<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: tablecaption.tag.php,v 1.6 2004/11/12 21:25:10 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Load parent
*/
require_once WACT_ROOT . 'template/tags/html/base.tag.php';
/**
* Represents a table Caption
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/HtmlTableCaptionTag
* @access protected
* @package WACT_TAG
*/
class HtmlTableCaptionTag extends HtmlBaseTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = '/template/components/html/html_tablecaption.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'HtmlTableCaptionComponent';

	/**
	* Constructs HtmlTableCaptionTag
	* @access protected
	*/
	function HtmlTableCaptionTag() {
		parent::HtmlBaseTag();
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$code->writeHTML('<caption');
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		if ( $align = $this->getAttribute('align') ) {
			$aligns = array('left','right','center','justify','');
			if ( in_array($align,$aligns) ) {
				$code->writeHTML(' align="'.$align.'"');
			}
		}
		parent::generateContents($code);
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$code->writeHTML("</caption>\n");
		parent::postGenerate($code);
	}
}
?>