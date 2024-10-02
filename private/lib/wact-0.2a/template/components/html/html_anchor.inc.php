<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: html_anchor.inc.php,v 1.1 2003/11/15 11:44:37 harryf Exp $
*/
//--------------------------------------------------------------------------------
/**
* Provides runtime API for HTML Anchor components, with simple event handling
* (EXPERIMENTAL)
* @see http://wact.sourceforge.net/index.php/HtmlAnchorComponent
* @access public
* @package WACT_COMPONENT
*/
class HtmlAnchorComponent extends Component {
	/**
	* Name of call back function to respond to "clicks"
	* @var string
	* @access private
	*/
	var $clickHandler;
	/**
	* Name of GET variable to "watch" for "clicks"
	* @var string
	* @access private
	*/
	var $clickField;

	/**
	* Sets the onclick call back function
	* @param string function name
	* @access protected
	* @return void
	*/
	function setClickHandler($handler) {
		$this->clickHandler = $handler;
	}
	/**
	* Sets the GET variable name to watch for clicks
	* @param string function name
	* @access protected
	* @return void
	*/
	function setClickField($field) {
		$this->clickField = $field;
	}
	/**
	* @return void
	* @access protected
	*/
	function prepare() {
		if ( isset($this->clickHandler) && isset($this->clickField) ) {
			if ( isset ( $_GET[$this->clickField] ) ) {
				$call = $this->clickHandler;
				$args = array(&$this,array('clicked'=>$this->clickField));
				call_user_func_array($call,$args);
			}
		}
	}
}
?>