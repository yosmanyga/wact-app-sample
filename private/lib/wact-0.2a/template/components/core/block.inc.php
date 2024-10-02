<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: block.inc.php,v 1.1 2004/06/12 11:21:26 harryf Exp $
*/
//--------------------------------------------------------------------------------
/**
* The block tag can be used to show or hide the contents of the block.
* The BlockComponent provides an API which allows the block to be shown
* or hidden at runtime.
* @see http://wact.sourceforge.net/index.php/BlockComponent
* @access public
* @package WACT_COMPONENT
*/
class BlockComponent extends Component {
	/**
	* Whether the block is visible or not
	* @var boolean
	* @access private
	*/
	var $visible = TRUE;
	/**
	* Called within the compiled template render function to determine
	* whether block should be displayed.
	* @return boolean current state of the block
	* @access protected
	*/
	function IsVisible() {
		return $this->visible;
	}

	/**
	* Changes the block state to visible
	* @return void
	* @access public
	*/
	function show() {
		$this->visible = TRUE;
	}

	/**
	* Changes the block state to invisible
	* @return void
	* @access public
	*/
	function hide() {
		$this->visible = FALSE;
	}
}
?>