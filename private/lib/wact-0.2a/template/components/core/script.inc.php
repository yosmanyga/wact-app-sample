<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: script.inc.php,v 1.1 2004/03/19 23:42:00 harryf Exp $
*/
//--------------------------------------------------------------------------------
/**
* The CoreScriptComponentallows JavaScript dynamically generated at
* runtime via the CoreScriptTag
* @see http://wact.sourceforge.net/index.php/CoreScriptComponent
* @see CoreScriptTag
* @access public
* @package WACT_COMPONENT
*/
class CoreScriptComponent extends Component {
	/**
	* @var string
	* @access private
	*/
	var $javascript;

	/**
	* Write some JavaScript into the container
	* @param string JavaScript to write to compiled template
	* @return void
	* @access public
	*/
	function writeJavaScript($javascript) {
		$this->javascript.=$javascript;
	}

	/**
	* Returns the JavaScript for display
	* @return string the JavaScript
	* @access public
	*/
	function readJavaScript() {
		return $this->javascript;
	}

}
?>