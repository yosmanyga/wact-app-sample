<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: bbcode.inc.php,v 1.4 2004/06/30 17:41:30 harryf Exp $
*/
//--------------------------------------------------------------------------------
/**
* Include PEAR::HTMLBBCodeParser
*/
if (!@include_once 'HTML/BBCodeParser.php') {
    RaiseError('runtime', 'LIBRARY_REQUIRED', array(
        'library' => 'PEAR::HTMLBBCodeParser v1.1+',
        'path' => 'include path: '.ini_get('include_path').': HTML/'));
}
/**
* The HtmlBBCodeComponent provides a runtime API for the html:BBCode tag.
* It wraps PEAR::HTML_BBCodeParser
* @see http://wact.sourceforge.net/index.php/HtmlBBCodeComponent
* @access public
* @package WACT_COMPONENT
*/
class HtmlBBCodeComponent extends Component {
	/**
	* PEAR::HTML_BBCodeParser options
	* @array
	* @access public
	*/
	var $options;
	/**
	* Text to parse for BBCodes
	* @text
	* @access private
	*/	
	var $text;
	/**
	* Set the text to parse for BBCodes
	* @param string
	* @return void
	* @access public
	*/
	function setText($text) {
		$this->text = $text;
	}
	/**
	* Returns the parsed text
	* @return string
	* @access public
	*/	
	function display() {
		$text = $this->text;
		if ( $this->options['wordwrap'] ) {
			$text = wordwrap($text,$this->options['wordwrap'],"\n");
		}
		if ( $this->options['htmlentities'] ) {
			$text = htmlentities($text, ENT_QUOTES);
		}
		$Parser = new HTML_BBCodeParser($this->options);
		$Parser->setText($text);
		$Parser->parse();
		$text = $Parser->getParsed();
		$text = str_replace('javascript','java_script',$text);
		if ( $this->options['nl2br'] ) {
			return nl2br($text);
		} else {
			return $text;
		}
	}
}
?>