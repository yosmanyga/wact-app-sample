<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: servercomponent.inc.php,v 1.12 2004/11/12 21:25:06 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* include parent class
*/
require_once WACT_ROOT . 'template/compiler/compilercomponent.inc.php';
/**
* Server component tags have a corresponding server Component which represents
* an API which can be used to manipulate the marked up portion of the template.
* @see http://wact.sourceforge.net/index.php/ServerComponentTag
* @access public
* @abstract
* @package WACT_TEMPLATE
*/
class ServerComponentTag extends CompilerComponent {
	/**
	* Returns a string of PHP code identifying the component in the hierarchy.
	* @return string
	* @access protected
	*/
	function getComponentRefCode() {
		$path = $this->parent->getComponentRefCode();
		return $path . '->children[\'' . $this->getServerId() . '\']';
	}

	/**
	* Calls the parent getComponentRefCode() method and writes it to the
	* compiled template, appending an addChild() method used to create
	* this component at runtime
	* @param CodeWriter
	* @return string
	* @access protected
	*/
	function generateConstructor(&$code) {
		if (isset($this->runtimeIncludeFile)) {
			$code->registerInclude(WACT_ROOT . $this->runtimeIncludeFile);
		}
		$code->writePHP($this->parent->getComponentRefCode() .
			'->addChild(new ' . $this->runtimeComponentName . 
			'(), \'' . $this->getServerId() . '\');');
		parent::generateConstructor($code);
	}
}
?>