<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: tablecell.tag.php,v 1.6 2004/11/12 21:25:10 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Load parent
*/
require_once WACT_ROOT . 'template/tags/html/base.tag.php';
/**
* Represents a table cell
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/HtmlTableCellTag
* @access protected
* @package WACT_TAG
*/
class HtmlTableCellTag extends HtmlBaseTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = '/template/components/html/html_tablecell.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'HtmlTableCellComponent';

	/**
	* Constructs HtmlTableCellTag
	* @access protected
	*/
	function HtmlTableCellTag() {
		parent::HtmlBaseTag();
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$code->writeHTML('<td');
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
		if ( $colspan = $this->getAttribute('colspan') ) {
			$code->writeHTML(' colspan="'.$colspan.'"');
		}
		if ( $rowspan = $this->getAttribute('rowspan') ) {
			$code->writeHTML(' rowspan="'.$rowspan.'"');
		}
		if ( $valign = $this->getAttribute('valign') ) {
			$valigns = array('top','middle','bottom','');
			if ( in_array($valign,$valigns) ) {
				$code->writeHTML(' valign="'.$valign."'");
			}
		}
		if ( $wrap = $this->getAttribute('wrap') ) {
			$wraps = array('true','false');
			if ( in_array($wrap,$wraps) ) {
				$code->writeHTML(' wrap="'.$wrap.'"');
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
		$code->writeHTML("</td>\n");
		parent::postGenerate($code);
	}
}
/**
* Represents a table header
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/HtmlTableHeaderTag
* @access protected
* @package WACT_TAG
*/
class HtmlTableHeaderTag extends HtmlTableCellTag {
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'HtmlTableHeaderComponent';
	/**
	* Constructs HtmlTableCellTag
	* @access protected
	*/
	function HtmlTableHeaderTag() {
		HtmlBaseTag::HtmlBaseTag();
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		HtmlBaseTag::preGenerate($code);
		$code->writeHTML('<th');
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$code->writeHTML("</th>\n");
		HtmlBaseTag::postGenerate($code);
	}
}
?>