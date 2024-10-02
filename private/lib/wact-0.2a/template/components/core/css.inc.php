<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: css.inc.php,v 1.1 2004/06/11 09:56:28 harryf Exp $
*/
//--------------------------------------------------------------------------------
/**
* The CoreCssComponent allows CSS dynamically generated at
* runtime via the CoreCssTag
* @see http://wact.sourceforge.net/index.php/CoreCssComponent
* @see CoreScriptTag
* @access public
* @package WACT_COMPONENT
*/
class CoreCssComponent extends Component {
	/**
	* @var string
	* @access private
	*/
	var $css;

	/**
	* Write some CSS into the container
	* @param string CSS to write to output
	* @return void
	* @access public
	*/
	function writeCSS($css) {
		$this->css.=$css;
	}

	/**
	* Returns the CSS for display
	* @return string the CSS
	* @access public
	*/
	function readCSS() {
		return $this->css;
	}

}
?>