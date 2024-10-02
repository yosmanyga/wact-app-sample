<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: servertagcomponent.inc.php,v 1.21 2004/11/12 06:56:24 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Server tag component tags are ServerComponentTags which also correspond to
* an HTML tag. Makes it easier to implement instead of extending from the
* ServerComponentTag class
* @see http://wact.sourceforge.net/index.php/ServerTagComponentTag
* @access public
* @abstract
* @package WACT_TEMPLATE
*/
class ServerTagComponentTag extends ServerComponentTag {

	/**
	* Returns the XML tag name
	* @return string
	* @access protected
	*/
	function getRenderedTag() {
		return $this->tag;
	}

	/**
	* Adds any additional XML attributes
	* @param CodeWriter
	* @return void
	* @abstract
	* @access protected
	*/
	function generateExtraAttributes(&$code) {
	    $this->generateDynamicAttributeList($code);
	}

	/**
	* Calls the parent preGenerate() method then writes the XML tag name
	* plus a PHP string which renders the attributes from the runtime
	* component.
	* @param CodeWriter
	* @return void
	* @access protected
	* @todo compiler needs to detect XML to allow for empty tags
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$code->writeHTML('<' . $this->getRenderedTag());
		$code->writePHP($this->getComponentRefCode() . '->renderAttributes();');
		if ( $this->emptyClosedTag ) {
			$code->writeHTML(' /');
		}
		$this->generateExtraAttributes($code);
		$code->writeHTML('>');
	}

	/**
	* Writes the closing tag string to the compiled template
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		if ($this->hasClosingTag) {
			$code->writeHTML('</' . $this->getRenderedTag() .  '>');
		}
		parent::postGenerate($code);
	}

	/**
	* Writes the compiled template constructor from the runtime component,
	* assigning the attributes found at compile time to the runtime component
	* via a serialized string
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateConstructor(&$code) {
		parent::generateConstructor($code);

        // Determine which attributes should not propigate to runtime
		$CompileTimeAttributes = $this->TagInfo->CompilerAttributes;

		// Add the runat attribute to the list of attributes to filter out
		$CompileTimeAttributes[] = PARSER_TRIGGER_ATTR_NAME;

		$code->writePHP($this->getComponentRefCode() . '->attributes = unserialize(');
		$code->writePHPLiteral(serialize($this->getAttributesAsArray($CompileTimeAttributes)));
        $code->writePHP(');');

	}
}
?>