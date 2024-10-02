<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: widgets.inc.php,v 1.1 2004/02/11 22:25:12 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* Widgets are runtime components which have no compile time template tag.
* They can be created and added by the PHP script controlling the template.
*/


/**
* Allows plain text to be added
* Widgets are runtime components which have no compile time template tag.
* They can be created and added by the PHP script controlling the template.
* @see http://wact.sourceforge.net/index.php/TextWidget
* @access public
* @package WACT_COMPONENT
*/
class TextWidget extends Component {
	/**
	* Text to add
	* @var string
	*/
	var $text;

	/**
	* Constructs TextComponent
	* @param string text to add
	* @access public
	*/
	function TextWidget($text) {
		$this->text = $text;
		$this->IsDynamicallyRendered = TRUE;
	}

	/**
	* Override parent method to prevent use of children
	* @return void
	* @access public
	*/
	function addChild() {
	    // Should we kick an error message here?
	}

	/**
	* Outputs the text Widget.
	* @return void
	* @access public
	*/
	function render() {
		echo ( htmlspecialchars($this->text, ENT_NOQUOTES) );
	}
}

/**
* Allows a tag to be created, which cannot contain children e.g. img
* @see http://wact.sourceforge.net/index.php/TagWidget
* @access public
* @package WACT_COMPONENT
*/
class TagWidget extends TagComponent {
	/**
	* Name of the tag
	* @var string
	* @access private
	*/
	var $tag;
	
	/**
	* Whether the tag is closing or not
	* @var boolean
	* @access private
	*/
	var $closing = true;
	
	/**
	* Constructs TagWidget
	* @param string name of tag
	* @param boolean whether tag is closing
	* @access public
	*/
	function TagWidget($tag,$closing=true) {
		$this->tag = htmlspecialchars($tag,ENT_QUOTES);
		$this->closing = $closing;
		$this->IsDynamicallyRendered = TRUE;
	}
	
	/**
	* Override parent method to prevent use of children
	* @return void
	* @access public
	*/
	function addChild() {
	    // Should we kick an error message here?
	}

	/**
	* Outputs the tag
	* @return void
	* @access public
	*/
	function render() {
		echo ( '<'.$this->tag );
		echo ( $this->renderAttributes());
		if ( $this->closing )
			echo ( '/>' );
		else
			echo ( '>' );
	}
}

/**
* Allows a tag to be created, which can contain children
* @see http://wact.sourceforge.net/index.php/TagContainerWidget
* @access public
* @package WACT_COMPONENT
*/
class TagContainerWidget extends TagComponent {
	/**
	* Name of the tag
	* @var string
	* @access private
	*/
	var $tag;
	
	/**
	* Constructs TagContainerWidget
	* @param string name of tag
	* @param boolean whether tag is closing
	* @access public
	*/
	function TagContainerWidget($tag) {
		$this->tag = htmlspecialchars($tag, ENT_QUOTES);
		$this->IsDynamicallyRendered = TRUE;
	}
	
	/**
	* Outputs the tag, rendering any child components as well
	* @return void
	* @access public
	*/
	function render() {
		echo ( '<'.$this->tag );
		echo ( $this->renderAttributes().'>');
		parent :: render();
		echo ( '</'.$this->tag.'>' );
	}
}
?>