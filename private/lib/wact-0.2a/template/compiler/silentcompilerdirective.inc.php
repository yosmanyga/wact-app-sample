<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: silentcompilerdirective.inc.php,v 1.5 2003/09/23 14:37:44 harryf Exp $
*/
//--------------------------------------------------------------------------------
/**
* Silent compiler directive tags are instructions for the compiler and do
* not have a corresponding runtime Component, nor do they normally generate
* output into the compiled template.
* @see http://wact.sourceforge.net/index.php/SilentCompilerDirectiveTag
* @access public
* @abstract
* @package WACT_TEMPLATE
*/
class SilentCompilerDirectiveTag extends CompilerComponent {
	/**
	* Does nothing -  SilentCompilerDirectiveTags do not generate 
	* during construction of the compiled template
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generate(&$code) {
		// Silent Compiler Directives do not generate their contents during the
		// normal generation sequence.
	}

	/**
	* Results in all components registered as children of the instance of this
	* component having their generate() methods called
	* @see CompilerComponent::generate
	* @param string code to generate
	* @return void
	* @access protected
	*/
	function generateNow(&$code) {
		return parent::generate($code);
	}
}
?>