<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: base.tag.php,v 1.5 2004/02/12 20:27:20 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Base tag (not directly used) for HTML tags
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/HtmlBaseTag
* @access protected
* @package WACT_TAG
*/
class HtmlBaseTag extends ServerComponentTag {
	/**
	* Instance of CssWriter
	* @var CssWriter
	* @access private
	*/
	var $CssWriter;

	/**
	* Constructs HtmlBaseTag
	* @access protected
	*/
	function HtmlBaseTag() {
		$this->CssWriter = & new CssWriter();
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateConstructor(&$code) {
		parent::generateConstructor($code);
		if ($this->getBoolAttribute('hide')) {
			$code->writePHP($this->getComponentRefCode() . '->visible = FALSE;');
		}
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$code->writePHP('if (' . $this->getComponentRefCode() . '->IsVisible()) {');
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		if ( $accesskey = $this->getAttribute('accesskey') ) {
			$code->writeHTML(' accesskey="'.$accesskey.'"');
		}
		if ( $background_color = $this->getAttribute('background-color') ) {
			$this->CssWriter->add('background-color',$background_color);
		}
		if ( $border_color = $this->getAttribute('border-color') ) {
			$this->CssWriter->add('border-color',$border_color);
		}
		if ( $border_style = $this->getAttribute('border-style') ) {
			$border_styles = array('none','dotted','dashed','solid',
				'double','groove','ridge','inset','outset','');
			if ( in_array($border_style,$border_styles) ) {
				$this->CssWriter->add('border-style',$border_style);
			}
		}
		if ( $border_width = $this->getAttribute('border-width') ) {
			$this->CssWriter->add('border-width',$border_width);
		}
		if ( $class = $this->getAttribute('class') ) {
			$code->writeHTML(' class="'.$class.'"');
		}
		if ( $color = $this->getAttribute('color') ) {
			$this->CssWriter->add('color',$color);
		}
		if ( $font = $this->getAttribute('font') ) {
			$this->CssWriter->add('font',$font);
		}
		if ( $font_family = $this->getAttribute('font-family') ) {
			$this->CssWriter->add('font-family',$font_family);
		}
		if ( $font_size = $this->getAttribute('font-size') ) {
			$this->CssWriter->add('font-size',$font_size);
		}
		if ( $font_weight = $this->getAttribute('font-weight') ) {
			$this->CssWriter->add('font-weight',$font_weight);
		}
		if ( $height = $this->getAttribute('height') ) {
			$this->CssWriter->add('height',$height);
		}
		if ( $id = $this->getAttribute('id') ) {
			$code->writeHTML(' id="'.$id.'"');
		}
		if ( $style = $this->getAttribute('style') ) {
			$code->writeHTML(' style="'.$style.'"');
		}
		if ( $tabindex = $this->getAttribute('tabindex') ) {
			$code->writeHTML(' tabindex="'.$tabindex.'"');
		}
		if ( $title = $this->getAttribute('title') ) {
			$code->writeHTML(' title="'.$title.'"');
		}
		if ( $width = $this->getAttribute('width') ) {
			$this->CssWriter->add('width',$width);
		}
		$this->CssWriter->render($code);
		$code->writeHtml('>');
		parent::generateContents($code);
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$code->writePHP('}');
		parent::postGenerate($code);
	}
}

/**
* Build a CSS string for use is style attributes
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/CssWriter
* @access protected
* @package WACT_TAG
*/
class CssWriter {
	var $css = '';
	var $sep = '';
	function add($name,$value) {
		$this->css.=$this->sep.$name.': '.$value.';';
		$this->sep  = ' ';
	}
	function render(&$code) {
		if ( !empty($this->css) ) {
			$code->writeHtml(' style="'.$this->css.'"');
		}
	}
}
?>