<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: table.tag.php,v 1.6 2004/11/12 21:25:10 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Load parent
*/
require_once WACT_ROOT . 'template/tags/html/base.tag.php';
/**
* For HTML tables
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/HtmlTableTag
* @access protected
* @package WACT_TAG
*/
class HtmlTableTag extends HtmlBaseTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = '/template/components/html/html_table.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'HtmlTableComponent';

	/**
	* Constructs HtmlTableCellTag
	* @access protected
	*/
	function HtmlTableTag() {
		parent::HtmlBaseTag();
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$code->writeHTML('<table');
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
		if ( $background_image = $this->getAttribute('background-image') ) {
			$this->CssWriter->add('background-image','url('.$background_image.')');
		}
		if ( $cellpadding = $this->getAttribute('cellpadding') ) {
			$code->writeHTML('cellpadding="'.$cellpadding.'"');
		}
		if ( $cellspacing = $this->getAttribute('cellspacing') ) {
			$code->writeHTML('cellspacing="'.$cellspacing.'"');
		}
		if ( $rule = $this->getAttribute('rules') ) {
			$rules = array('all','none','cols','rows','');
			if ( in_array($rule,$rules) ) {
				$code->writeHTML(' rules="'.$rule.'"');
				if ( !$this->getAttribute('border-style') ) {
					$code->writeHTML(' border="1"');
				}
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
		$code->writeHTML("</table>\n");
		parent::postGenerate($code);
	}
}
?>