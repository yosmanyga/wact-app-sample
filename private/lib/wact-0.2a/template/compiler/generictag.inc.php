<?php 
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: generictag.inc.php,v 1.2 2004/03/15 12:35:08 harryf Exp $
*/
//--------------------------------------------------------------------------------
/**
* Compile time component for tags in template which are not
* recognized WACT tags but have a runat="server" attribute.
* This allows native HTML tags, for example, to be manipulated
* at runtime.
* GenericTag is for tags which no children.
* @see http://wact.sourceforge.net/index.php/GenericTag
* @access protected
* @package WACT_TEMPLATE
*/
class GenericTag extends ServerTagComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile;
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'TagComponent';
}

/**
* GenericContainerTag is for GenericTags which can have children
* and can have widgets added to them at runtime.
* @see http://wact.sourceforge.net/index.php/GenericContainerTag
* @see GenericTag
* @access protected
* @package WACT_TEMPLATE
*/
class GenericContainerTag extends ServerTagComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile;
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'TagComponent';
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateConstructor(&$code) {
		parent::generateConstructor($code);
        $code->writePHP($this->getComponentRefCode() . 
            '->IsDynamicallyRendered = TRUE;');
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(& $code) {
		$code->writePHP($this->getComponentRefCode() . '->render();');
		parent::postGenerate($code);
	}
}
?>