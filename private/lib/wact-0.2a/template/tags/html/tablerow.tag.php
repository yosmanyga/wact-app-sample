<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: tablerow.tag.php,v 1.6 2004/11/12 21:25:10 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Load parent
*/
require_once WACT_ROOT . 'template/tags/html/base.tag.php';
/**
* Base tag (not directly used) for HTML tags
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/HtmlTableRowTag
* @access protected
* @package WACT_TAG
*/
class HtmlTableRowTag extends HtmlBaseTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = '/template/components/html/html_tablerow.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'HtmlTableRowComponent';

	/**
	* Constructs HtmlTableCellTag
	* @access protected
	*/
	function HtmlTableRowTag() {
		parent::HtmlBaseTag();
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$code->writeHTML('<tr');
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
		if ( $valign = $this->getAttribute('valign') ) {
			$valigns = array('top','middle','bottom','');
			if ( in_array($valign,$valigns) ) {
				$code->writeHTML(' valign="'.$valign.'"');
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
		$code->writeHTML("</tr>\n");
		parent::postGenerate($code);
	}
}
?>